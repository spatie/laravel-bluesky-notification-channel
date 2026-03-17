<?php

namespace Spatie\BlueskyNotificationChannel;

/** @internal */
final class BlueskyIdentity
{
    public function __construct(
        public readonly string $did,
        public readonly string $handle,
        public readonly string $email,
        public readonly string $accessJwt,
        public readonly string $refreshJwt,
    ) {}
}
