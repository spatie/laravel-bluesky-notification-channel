<?php

namespace Spatie\BlueskyNotificationChannel\IdentityRepository;

use Illuminate\Cache\Repository as CacheRepository;
use Spatie\BlueskyNotificationChannel\BlueskyIdentity;
use Spatie\BlueskyNotificationChannel\Exceptions\NoBlueskyIdentityFound;

class IdentityRepositoryUsingCache implements IdentityRepository
{
    public const DEFAULT_CACHE_KEY = 'bluesky-notification-channel:identity';

    public function __construct(
        private readonly CacheRepository $cache,
        private readonly string $key,
    ) {}

    public function hasIdentity(): bool
    {
        return $this->cache->get($this->key) instanceof BlueskyIdentity;
    }

    public function getIdentity(): BlueskyIdentity
    {
        $identity = $this->cache->get($this->key);

        if (! $identity instanceof BlueskyIdentity) {
            throw NoBlueskyIdentityFound::create();
        }

        return $identity;
    }

    public function setIdentity(BlueskyIdentity $identity): void
    {
        $this->cache->set(
            key: $this->key,
            value: $identity,
        );
    }

    public function clearIdentity(): void
    {
        $this->cache->forget($this->key);
    }
}
