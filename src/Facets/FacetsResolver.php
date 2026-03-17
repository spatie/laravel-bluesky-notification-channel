<?php

namespace Spatie\BlueskyNotificationChannel\Facets;

use Spatie\BlueskyNotificationChannel\BlueskyPost;
use Spatie\BlueskyNotificationChannel\BlueskyService;

interface FacetsResolver
{
    /** @return Facet[] */
    public function resolve(BlueskyService $bluesky, BlueskyPost $post): array;
}
