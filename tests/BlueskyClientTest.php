<?php

use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Support\Facades\Http;
use Spatie\BlueskyNotificationChannel\Blob;
use Spatie\BlueskyNotificationChannel\BlueskyClient;
use Spatie\BlueskyNotificationChannel\BlueskyIdentity;
use Spatie\BlueskyNotificationChannel\BlueskyPost;
use Spatie\BlueskyNotificationChannel\Embeds\LinkEmbedResolverUsingCardyb;
use Spatie\BlueskyNotificationChannel\Exceptions\CouldNotCreatePost;
use Spatie\BlueskyNotificationChannel\Exceptions\CouldNotCreateSession;
use Spatie\BlueskyNotificationChannel\Exceptions\CouldNotRefreshSession;
use Spatie\BlueskyNotificationChannel\Exceptions\CouldNotResolveHandle;
use Spatie\BlueskyNotificationChannel\Exceptions\CouldNotUploadBlob;

beforeEach(function () {
    $this->identity = new BlueskyIdentity(
        did: 'did:plc:test123',
        handle: 'test.bsky.social',
        email: 'test@example.com',
        accessJwt: 'access-jwt-token',
        refreshJwt: 'refresh-jwt-token',
    );
});

function createClient(): BlueskyClient
{
    return new BlueskyClient(
        httpClient: app(HttpClient::class),
        embedResolver: new LinkEmbedResolverUsingCardyb(
            httpClient: app(HttpClient::class),
        ),
        baseUrl: BlueskyClient::DEFAULT_BASE_URL,
        username: 'test.bsky.social',
        password: 'test-password',
    );
}

it('creates a session', function () {
    Http::fake([
        '*/com.atproto.server.createSession' => Http::response([
            'did' => 'did:plc:test123',
            'handle' => 'test.bsky.social',
            'email' => 'test@example.com',
            'accessJwt' => 'access-token',
            'refreshJwt' => 'refresh-token',
        ]),
    ]);

    $identity = createClient()->createIdentity();

    expect($identity)
        ->toBeInstanceOf(BlueskyIdentity::class)
        ->did->toBe('did:plc:test123')
        ->handle->toBe('test.bsky.social')
        ->email->toBe('test@example.com')
        ->accessJwt->toBe('access-token')
        ->refreshJwt->toBe('refresh-token');
});

it('throws on failed session creation', function () {
    Http::fake([
        '*/com.atproto.server.createSession' => Http::response([
            'error' => 'AuthenticationRequired',
            'message' => 'Invalid identifier or password',
        ], 401),
    ]);

    createClient()->createIdentity();
})->throws(CouldNotCreateSession::class);

it('refreshes a session', function () {
    Http::fake([
        '*/com.atproto.server.refreshSession' => Http::response([
            'did' => 'did:plc:test123',
            'handle' => 'test.bsky.social',
            'accessJwt' => 'new-access-token',
            'refreshJwt' => 'new-refresh-token',
        ]),
    ]);

    $refreshedIdentity = createClient()->refreshIdentity($this->identity);

    expect($refreshedIdentity)
        ->toBeInstanceOf(BlueskyIdentity::class)
        ->did->toBe('did:plc:test123')
        ->handle->toBe('test.bsky.social')
        ->email->toBe('test@example.com')
        ->accessJwt->toBe('new-access-token')
        ->refreshJwt->toBe('new-refresh-token');
});

it('throws on failed session refresh', function () {
    Http::fake([
        '*/com.atproto.server.refreshSession' => Http::response([
            'error' => 'ExpiredToken',
            'message' => 'Token has expired',
        ], 400),
    ]);

    createClient()->refreshIdentity($this->identity);
})->throws(CouldNotRefreshSession::class);

it('creates a post', function () {
    Http::fake([
        '*/com.atproto.repo.createRecord' => Http::response([
            'uri' => 'at://did:plc:test123/app.bsky.feed.post/abc123',
            'cid' => 'bafyreiabc123',
        ]),
    ]);

    $post = BlueskyPost::make()->text('Hello, Bluesky!');

    $uri = createClient()->createPost($this->identity, $post);

    expect($uri)->toBe('at://did:plc:test123/app.bsky.feed.post/abc123');
});

it('throws on failed post creation', function () {
    Http::fake([
        '*/com.atproto.repo.createRecord' => Http::response([
            'error' => 'InvalidRequest',
            'message' => 'Could not create record',
        ], 400),
    ]);

    $post = BlueskyPost::make()->text('Hello, Bluesky!');

    createClient()->createPost($this->identity, $post);
})->throws(CouldNotCreatePost::class);

it('resolves a handle', function () {
    Http::fake([
        '*/com.atproto.identity.resolveHandle*' => Http::response([
            'did' => 'did:plc:resolved123',
        ]),
    ]);

    $did = createClient()->resolveHandle('someone.bsky.social');

    expect($did)->toBe('did:plc:resolved123');
});

it('throws on failed handle resolution', function () {
    Http::fake([
        '*/com.atproto.identity.resolveHandle*' => Http::response([
            'error' => 'InvalidRequest',
            'message' => 'Unable to resolve handle',
        ], 400),
    ]);

    createClient()->resolveHandle('nonexistent.bsky.social');
})->throws(CouldNotResolveHandle::class);

it('uploads a blob', function () {
    $blobData = [
        '$type' => 'blob',
        'ref' => ['$link' => 'bafkreiblob123'],
        'mimeType' => 'image/png',
        'size' => 12345,
    ];

    Http::fake([
        '*/com.atproto.repo.uploadBlob' => Http::response([
            'blob' => $blobData,
        ]),
        'https://example.com/image.png' => Http::response('fake-image-content'),
    ]);

    $blob = createClient()->uploadBlob($this->identity, 'https://example.com/image.png');

    expect($blob)
        ->toBeInstanceOf(Blob::class)
        ->toArray()->toBe($blobData);
});

it('throws when image cannot be loaded for blob upload', function () {
    Http::fake([
        '*' => Http::response('', 404),
    ]);

    createClient()->uploadBlob($this->identity, 'https://example.com/nonexistent.png');
})->throws(CouldNotUploadBlob::class);
