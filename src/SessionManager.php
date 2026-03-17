<?php

namespace Spatie\BlueskyNotificationChannel;

use Spatie\BlueskyNotificationChannel\IdentityRepository\IdentityRepository;

class SessionManager
{
    public function __construct(
        private readonly BlueskyClient $client,
        private readonly IdentityRepository $identityRepository,
    ) {}

    public function getIdentity(): BlueskyIdentity
    {
        $this->ensureHasIdentity();
        $this->refreshIdentity();

        return $this->identityRepository->getIdentity();
    }

    private function ensureHasIdentity(): void
    {
        if ($this->identityRepository->hasIdentity()) {
            return;
        }

        $this->identityRepository->setIdentity(
            identity: $this->client->createIdentity(),
        );
    }

    private function refreshIdentity(): void
    {
        $identity = $this->client->refreshIdentity(
            identity: $this->identityRepository->getIdentity(),
        );

        $this->identityRepository->setIdentity($identity);
    }
}
