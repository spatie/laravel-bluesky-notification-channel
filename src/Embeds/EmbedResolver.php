<?php

namespace Spatie\BlueskyNotificationChannel\Embeds;

use Spatie\BlueskyNotificationChannel\BlueskyPost;
use Spatie\BlueskyNotificationChannel\BlueskyService;

interface EmbedResolver
{
    public function resolve(BlueskyService $bluesky, BlueskyPost $post): ?Embed;

    public function createEmbedFromUrl(BlueskyService $bluesky, string $url): ?Embed;
}
