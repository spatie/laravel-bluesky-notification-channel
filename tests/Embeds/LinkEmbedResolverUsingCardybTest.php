<?php

use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Support\Facades\Http;
use Spatie\BlueskyNotificationChannel\BlueskyIdentity;
use Spatie\BlueskyNotificationChannel\BlueskyPost;
use Spatie\BlueskyNotificationChannel\BlueskyService;
use Spatie\BlueskyNotificationChannel\Embeds\External;
use Spatie\BlueskyNotificationChannel\Embeds\LinkEmbedResolverUsingCardyb;
use Spatie\BlueskyNotificationChannel\Facets\Facet;
use Spatie\BlueskyNotificationChannel\Facets\Link;
use Spatie\BlueskyNotificationChannel\IdentityRepository\IdentityRepository;

beforeEach(function () {
    Http::fake([
        '*/com.atproto.server.createSession' => Http::response([
            'did' => 'did:plc:test123',
            'handle' => 'test.bsky.social',
            'email' => 'test@example.com',
            'accessJwt' => 'access-token',
            'refreshJwt' => 'refresh-token',
        ]),
        '*/com.atproto.server.refreshSession' => Http::response([
            'did' => 'did:plc:test123',
            'handle' => 'test.bsky.social',
            'accessJwt' => 'refreshed-access-token',
            'refreshJwt' => 'refreshed-refresh-token',
        ]),
    ]);

    $identity = new BlueskyIdentity(
        did: 'did:plc:test123',
        handle: 'test.bsky.social',
        email: 'test@example.com',
        accessJwt: 'access-token',
        refreshJwt: 'refresh-token',
    );

    app(IdentityRepository::class)->setIdentity($identity);
});

it('resolves embed from first link facet', function () {
    Http::fake([
        'https://cardyb.bsky.app/*' => Http::response([
            'url' => 'https://example.com',
            'title' => 'Example Site',
            'description' => 'An example website',
            'image' => 'https://example.com/thumb.jpg',
        ]),
        '*/com.atproto.repo.uploadBlob' => Http::response([
            'blob' => [
                '$type' => 'blob',
                'ref' => ['$link' => 'bafkreiblob123'],
                'mimeType' => 'image/jpeg',
                'size' => 5000,
            ],
        ]),
        'https://example.com/thumb.jpg' => Http::response('fake-image-content'),
        '*/com.atproto.server.refreshSession' => Http::response([
            'did' => 'did:plc:test123',
            'handle' => 'test.bsky.social',
            'accessJwt' => 'refreshed-access-token',
            'refreshJwt' => 'refreshed-refresh-token',
        ]),
    ]);

    $resolver = new LinkEmbedResolverUsingCardyb(
        httpClient: app(HttpClient::class),
    );

    $facet = new Facet(
        range: [10, 29],
        features: [new Link(uri: 'https://example.com')],
    );

    $post = BlueskyPost::make()
        ->text('Check out https://example.com for more')
        ->facet($facet);

    $embed = $resolver->resolve(app(BlueskyService::class), $post);

    expect($embed)
        ->toBeInstanceOf(External::class)
        ->uri->toBe('https://example.com')
        ->title->toBe('Example Site')
        ->description->toBe('An example website');
});

it('returns null when no link facets exist', function () {
    $resolver = new LinkEmbedResolverUsingCardyb(
        httpClient: app(HttpClient::class),
    );

    $post = BlueskyPost::make()->text('Hello, no links here');

    $embed = $resolver->resolve(app(BlueskyService::class), $post);

    expect($embed)->toBeNull();
});

it('returns null when cardyb returns an error', function () {
    Http::fake([
        'https://cardyb.bsky.app/*' => Http::response([
            'error' => 'could not resolve url',
        ]),
        '*/com.atproto.server.refreshSession' => Http::response([
            'did' => 'did:plc:test123',
            'handle' => 'test.bsky.social',
            'accessJwt' => 'refreshed-access-token',
            'refreshJwt' => 'refreshed-refresh-token',
        ]),
    ]);

    $resolver = new LinkEmbedResolverUsingCardyb(
        httpClient: app(HttpClient::class),
    );

    $facet = new Facet(
        range: [10, 29],
        features: [new Link(uri: 'https://example.com')],
    );

    $post = BlueskyPost::make()
        ->text('Check out https://example.com for more')
        ->facet($facet);

    $embed = $resolver->resolve(app(BlueskyService::class), $post);

    expect($embed)->toBeNull();
});
