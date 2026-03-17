<?php

use Spatie\BlueskyNotificationChannel\BlueskyIdentity;
use Spatie\BlueskyNotificationChannel\Exceptions\NoBlueskyIdentityFound;
use Spatie\BlueskyNotificationChannel\IdentityRepository\IdentityRepository;

it('stores and retrieves identity', function () {
    $identity = new BlueskyIdentity(
        did: 'did:plc:test123',
        handle: 'test.bsky.social',
        email: 'test@example.com',
        accessJwt: 'access-token',
        refreshJwt: 'refresh-token',
    );

    $repository = app(IdentityRepository::class);

    $repository->setIdentity($identity);

    $retrieved = $repository->getIdentity();

    expect($retrieved)
        ->toBeInstanceOf(BlueskyIdentity::class)
        ->did->toBe('did:plc:test123')
        ->handle->toBe('test.bsky.social')
        ->email->toBe('test@example.com')
        ->accessJwt->toBe('access-token')
        ->refreshJwt->toBe('refresh-token');
});

it('reports has identity correctly', function () {
    $repository = app(IdentityRepository::class);

    expect($repository->hasIdentity())->toBeFalse();

    $identity = new BlueskyIdentity(
        did: 'did:plc:test123',
        handle: 'test.bsky.social',
        email: 'test@example.com',
        accessJwt: 'access-token',
        refreshJwt: 'refresh-token',
    );

    $repository->setIdentity($identity);

    expect($repository->hasIdentity())->toBeTrue();
});

it('throws when no identity exists', function () {
    $repository = app(IdentityRepository::class);

    $repository->getIdentity();
})->throws(NoBlueskyIdentityFound::class);

it('clears identity', function () {
    $identity = new BlueskyIdentity(
        did: 'did:plc:test123',
        handle: 'test.bsky.social',
        email: 'test@example.com',
        accessJwt: 'access-token',
        refreshJwt: 'refresh-token',
    );

    $repository = app(IdentityRepository::class);

    $repository->setIdentity($identity);

    expect($repository->hasIdentity())->toBeTrue();

    $repository->clearIdentity();

    expect($repository->hasIdentity())->toBeFalse();
});
