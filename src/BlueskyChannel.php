<?php

namespace Spatie\BlueskyNotificationChannel;

use Illuminate\Notifications\Notification;
use Spatie\BlueskyNotificationChannel\Exceptions\NoBlueskyChannel;

class BlueskyChannel
{
    public function __construct(
        protected readonly BlueskyService $bluesky,
    ) {}

    public function send(mixed $notifiable, Notification $notification): string
    {
        if (! method_exists($notification, 'toBluesky')) {
            throw NoBlueskyChannel::create(\get_class($notification));
        }

        return $this->bluesky->createPost(
            post: $notification->toBluesky($notifiable),
        );
    }
}
