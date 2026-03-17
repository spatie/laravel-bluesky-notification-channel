<?php

use Illuminate\Support\Facades\Http;
use Spatie\BlueskyNotificationChannel\BlueskyIdentity;
use Spatie\BlueskyNotificationChannel\IdentityRepository\IdentityRepository;
use Spatie\BlueskyNotificationChannel\SessionManager;

it('creates a new session when none exists', function () {
    Http::fake([
        '*/com.atproto.server.createSession' => Http::response([
            'did' => 'did:plc:test123',
            'handle' => 'test.bsky.social',
            'email' => 'test@example.com',
            'accessJwt' => 'access-token',
            'refreshJwt' => 'refresh-token',
        ]),
        '*/com.atproto.server.refreshSession' => Http::response([
            'did' => 'did:plc:test123',
            'handle' => 'test.bsky.social',
            'accessJwt' => 'refreshed-access-token',
            'refreshJwt' => 'refreshed-refresh-token',
        ]),
    ]);

    $sessionManager = app(SessionManager::class);

    $identity = $sessionManager->getIdentity();

    expect($identity)
        ->toBeInstanceOf(BlueskyIdentity::class)
        ->did->toBe('did:plc:test123')
        ->accessJwt->toBe('refreshed-access-token')
        ->refreshJwt->toBe('refreshed-refresh-token');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'com.atproto.server.createSession');
    });

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'com.atproto.server.refreshSession');
    });
});

it('refreshes an existing session', function () {
    Http::fake([
        '*/com.atproto.server.refreshSession' => Http::response([
            'did' => 'did:plc:test123',
            'handle' => 'test.bsky.social',
            'accessJwt' => 'refreshed-access-token',
            'refreshJwt' => 'refreshed-refresh-token',
        ]),
    ]);

    $existingIdentity = new BlueskyIdentity(
        did: 'did:plc:test123',
        handle: 'test.bsky.social',
        email: 'test@example.com',
        accessJwt: 'old-access-token',
        refreshJwt: 'old-refresh-token',
    );

    $identityRepository = app(IdentityRepository::class);
    $identityRepository->setIdentity($existingIdentity);

    $sessionManager = app(SessionManager::class);

    $identity = $sessionManager->getIdentity();

    expect($identity)
        ->toBeInstanceOf(BlueskyIdentity::class)
        ->accessJwt->toBe('refreshed-access-token')
        ->refreshJwt->toBe('refreshed-refresh-token');

    Http::assertNotSent(function ($request) {
        return str_contains($request->url(), 'com.atproto.server.createSession');
    });
});
