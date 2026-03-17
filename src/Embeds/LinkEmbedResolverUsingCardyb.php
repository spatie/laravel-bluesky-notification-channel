<?php

namespace Spatie\BlueskyNotificationChannel\Embeds;

use Illuminate\Http\Client\Factory as HttpClient;
use Spatie\BlueskyNotificationChannel\BlueskyPost;
use Spatie\BlueskyNotificationChannel\BlueskyService;
use Spatie\BlueskyNotificationChannel\Facets\Link;

final class LinkEmbedResolverUsingCardyb implements EmbedResolver
{
    public const ENDPOINT = 'https://cardyb.bsky.app/v1/extract';

    public function __construct(
        private readonly HttpClient $httpClient,
    ) {}

    public function resolve(BlueskyService $bluesky, BlueskyPost $post): ?Embed
    {
        $link = $this->findFirstLink($post);

        if (! $link) {
            return null;
        }

        return $this->createEmbedFromUrl($bluesky, $link->uri);
    }

    private function findFirstLink(BlueskyPost $post): ?Link
    {
        foreach ($post->facets as $facet) {
            foreach ($facet->getFeatures() as $feature) {
                if ($feature instanceof Link) {
                    return $feature;
                }
            }
        }

        return null;
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
