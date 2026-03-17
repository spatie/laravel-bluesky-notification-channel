<?php

namespace Spatie\BlueskyNotificationChannel\IdentityRepository;

use Spatie\BlueskyNotificationChannel\BlueskyIdentity;

interface IdentityRepository
{
    public function hasIdentity(): bool;

    public function getIdentity(): BlueskyIdentity;

    public function setIdentity(BlueskyIdentity $identity): void;

    public function clearIdentity(): void;
}
