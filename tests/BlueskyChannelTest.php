<?php

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Spatie\BlueskyNotificationChannel\BlueskyChannel;
use Spatie\BlueskyNotificationChannel\BlueskyPost;
use Spatie\BlueskyNotificationChannel\Exceptions\NoBlueskyChannel;

it('sends a notification via the bluesky channel', function () {
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
        '*/com.atproto.repo.createRecord' => Http::response([
            'uri' => 'at://did:plc:test123/app.bsky.feed.post/abc123',
            'cid' => 'bafyreiabc123',
        ]),
        'https://cardyb.bsky.app/*' => Http::response([
            'error' => 'no link found',
        ]),
    ]);

    $notification = new class extends Notification
    {
        public function toBluesky(mixed $notifiable): BlueskyPost
        {
            return BlueskyPost::make()->text('Test notification');
        }
    };

    $channel = app(BlueskyChannel::class);

    $uri = $channel->send(null, $notification);

    expect($uri)->toBe('at://did:plc:test123/app.bsky.feed.post/abc123');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'com.atproto.repo.createRecord');
    });
});

it('throws when notification does not have toBluesky method', function () {
    $notification = new class extends Notification {};

    $channel = app(BlueskyChannel::class);

    $channel->send(null, $notification);
})->throws(NoBlueskyChannel::class);
