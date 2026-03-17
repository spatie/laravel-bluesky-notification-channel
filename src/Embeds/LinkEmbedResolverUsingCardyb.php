<?php

namespace Spatie\BlueskyNotificationChannel\Embeds;

use Illuminate\Http\Client\Factory as HttpClient;
use Spatie\BlueskyNotificationChannel\BlueskyPost;
use Spatie\BlueskyNotificationChannel\BlueskyService;
use Spatie\BlueskyNotificationChannel\Facets\Facet;
use Spatie\BlueskyNotificationChannel\Facets\Feature;
use Spatie\BlueskyNotificationChannel\Facets\Link;

final class LinkEmbedResolverUsingCardyb implements EmbedResolver
{
    public const ENDPOINT = 'https://cardyb.bsky.app/v1/extract';

    public function __construct(
        private readonly HttpClient $httpClient,
    ) {}

    public function resolve(BlueskyService $bluesky, BlueskyPost $post): ?Embed
    {
        if (\count($post->facets) === 0) {
            return null;
        }

        $firstLink = array_find(
            $post->facets,
            fn (Facet $facet) => array_find(
                $facet->getFeatures(),
                fn (Feature $feature) => $feature->getType() === 'app.bsky.richtext.facet#link',
            ),
        );

        if (! $firstLink) {
            return null;
        }

        $linkFeature = array_find(
            $firstLink->getFeatures(),
            fn (Feature $feature) => $feature instanceof Link,
        );

        if (! $linkFeature instanceof Link) {
            return null;
        }

        return $this->createEmbedFromUrl($bluesky, $linkFeature->uri);
    }

    public function createEmbedFromUrl(BlueskyService $bluesky, string $url): ?Embed
    {
        $embed = $this->httpClient->get(self::ENDPOINT, [
            'url' => $url,
        ]);

        if ($embed->json('error')) {
            return null;
        }

        return new External(
            uri: $embed->json('url'),
            title: $embed->json('title'),
            description: $embed->json('description'),
            thumb: $bluesky
                ->uploadBlob($embed->json('image'))
                ->toArray(),
        );
    }
}
