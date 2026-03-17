<?php

use Illuminate\Support\Facades\Http;
use Spatie\BlueskyNotificationChannel\BlueskyPost;
use Spatie\BlueskyNotificationChannel\BlueskyService;
use Spatie\BlueskyNotificationChannel\Facets\DefaultFacetsResolver;
use Spatie\BlueskyNotificationChannel\Facets\Link;
use Spatie\BlueskyNotificationChannel\Facets\Mention;
use Spatie\BlueskyNotificationChannel\Facets\Tag;

it('detects links in text', function () {
    $resolver = new DefaultFacetsResolver;

    $post = BlueskyPost::make()->text('Check out https://example.com for more');

    $facets = $resolver->resolve(app(BlueskyService::class), $post);

    expect($facets)->toHaveCount(1);

    $features = $facets[0]->getFeatures();

    expect($features)->toHaveCount(1);
    expect($features[0])
        ->toBeInstanceOf(Link::class)
        ->uri->toBe('https://example.com');
});

it('detects mentions in text', function () {
    Http::fake([
        '*/com.atproto.identity.resolveHandle*' => Http::response([
            'did' => 'did:plc:mentioned123',
        ]),
        '*/com.atproto.server.createSession' => Http::response([
            'did' => 'did:plc:test123',
            'handle' => 'test.bsky.social',
            'email' => 'test@example.com',
            'accessJwt' => 'access-token',
            'refreshJwt' => 'refresh-token',
        ]),
    ]);

    $resolver = new DefaultFacetsResolver;

    $post = BlueskyPost::make()->text('Hello @someone.bsky.social!');

    $facets = $resolver->resolve(app(BlueskyService::class), $post);

    $mentionFacets = array_values(array_filter($facets, fn ($facet) => $facet->getFeatures()[0] instanceof Mention));

    expect($mentionFacets)->toHaveCount(1);
    expect($mentionFacets[0]->getFeatures()[0])
        ->toBeInstanceOf(Mention::class)
        ->did->toBe('did:plc:mentioned123');
});

it('detects hashtags in text', function () {
    $resolver = new DefaultFacetsResolver;

    $post = BlueskyPost::make()->text('Hello world #bluesky');

    $facets = $resolver->resolve(app(BlueskyService::class), $post);

    $tagFacets = array_values(array_filter($facets, fn ($facet) => $facet->getFeatures()[0] instanceof Tag));

    expect($tagFacets)->toHaveCount(1);
    expect($tagFacets[0]->getFeatures()[0])
        ->toBeInstanceOf(Tag::class)
        ->tag->toBe('bluesky');
});

it('skips unresolvable mentions', function () {
    Http::fake([
        '*/com.atproto.identity.resolveHandle*' => Http::response([
            'error' => 'InvalidRequest',
            'message' => 'Unable to resolve handle',
        ], 400),
        '*/com.atproto.server.createSession' => Http::response([
            'did' => 'did:plc:test123',
            'handle' => 'test.bsky.social',
            'email' => 'test@example.com',
            'accessJwt' => 'access-token',
            'refreshJwt' => 'refresh-token',
        ]),
    ]);

    $resolver = new DefaultFacetsResolver;

    $post = BlueskyPost::make()->text('Hello @nonexistent.handle.xyz!');

    $facets = $resolver->resolve(app(BlueskyService::class), $post);

    $mentionFacets = array_filter($facets, fn ($facet) => $facet->getFeatures()[0] instanceof Mention);

    expect($mentionFacets)->toBeEmpty();
});
