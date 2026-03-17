<?php

use Spatie\BlueskyNotificationChannel\BlueskyPost;
use Spatie\BlueskyNotificationChannel\Embeds\External;
use Spatie\BlueskyNotificationChannel\Facets\Facet;
use Spatie\BlueskyNotificationChannel\Facets\Link;

it('creates a post with text', function () {
    $post = BlueskyPost::make()->text('Hello, Bluesky!');

    expect($post->toArray())->toBe([
        'text' => 'Hello, Bluesky!',
    ]);
});

it('creates a post with language', function () {
    $post = BlueskyPost::make()
        ->text('Hello, Bluesky!')
        ->language('en');

    expect($post->toArray())->toBe([
        'text' => 'Hello, Bluesky!',
        'langs' => ['en'],
    ]);
});

it('creates a post with multiple languages', function () {
    $post = BlueskyPost::make()
        ->text('Hello, Bluesky!')
        ->language(['en', 'nl']);

    expect($post->toArray())->toBe([
        'text' => 'Hello, Bluesky!',
        'langs' => ['en', 'nl'],
    ]);
});

it('creates a post with an embed', function () {
    $embed = new External(
        uri: 'https://example.com',
        title: 'Example',
        description: 'An example link',
    );

    $post = BlueskyPost::make()
        ->text('Check this out')
        ->embed($embed);

    $result = $post->toArray();

    expect($result['embed'])->toBe([
        '$type' => 'app.bsky.embed.external',
        'external' => [
            'uri' => 'https://example.com',
            'title' => 'Example',
            'description' => 'An example link',
        ],
    ]);
});

it('creates a post with facets', function () {
    $facet = new Facet(
        range: [0, 23],
        features: [new Link(uri: 'https://example.com')],
    );

    $post = BlueskyPost::make()
        ->text('https://example.com test')
        ->facet($facet);

    $result = $post->toArray();

    expect($result['facets'])->toBe([
        [
            '$type' => 'app.bsky.richtext.facet',
            'index' => [
                'byteStart' => 0,
                'byteEnd' => 23,
            ],
            'features' => [
                [
                    '$type' => 'app.bsky.richtext.facet#link',
                    'uri' => 'https://example.com',
                ],
            ],
        ],
    ]);
});

it('can disable automatic embeds', function () {
    $post = BlueskyPost::make()
        ->text('Hello, Bluesky!')
        ->withoutAutomaticEmbeds();

    expect($post->automaticallyResolvesEmbeds())->toBeFalse();
});

it('can disable automatic facets', function () {
    $post = BlueskyPost::make()
        ->text('Hello, Bluesky!')
        ->withoutAutomaticFacets();

    expect($post->automaticallyResolvesFacets())->toBeFalse();
});

it('has automatic embeds enabled by default', function () {
    $post = BlueskyPost::make();

    expect($post->automaticallyResolvesEmbeds())->toBeTrue();
});

it('has automatic facets enabled by default', function () {
    $post = BlueskyPost::make();

    expect($post->automaticallyResolvesFacets())->toBeTrue();
});
