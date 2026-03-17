<?php

namespace Spatie\BlueskyNotificationChannel;

final class Blob
{
    public function __construct(
        public readonly array $blob,
    ) {}

    public function toArray(): array
    {
        return $this->blob;
    }
}
