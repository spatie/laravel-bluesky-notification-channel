# Send Bluesky notifications in Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-bluesky-notification-channel.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-bluesky-notification-channel)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/spatie/laravel-bluesky-notification-channel/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/spatie/laravel-bluesky-notification-channel/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/spatie/laravel-bluesky-notification-channel/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/spatie/laravel-bluesky-notification-channel/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-bluesky-notification-channel.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-bluesky-notification-channel)

This package makes it easy to send notifications to [Bluesky](https://bsky.app) using Laravel's notification system. Links, mentions and hashtags are automatically detected and rendered as rich text.

Here's how you can use it:

```php
BlueskyPost::make()
    ->text('Hello from Laravel! Check out https://spatie.be')
    ->language('en');
```

In a notification:

```php
<?php

use Illuminate\Notifications\Notification;
use Spatie\BlueskyNotificationChannel\BlueskyChannel;
use Spatie\BlueskyNotificationChannel\BlueskyPost;

class ServerDownNotification extends Notification
{
    public function via($notifiable): array
    {
        return [BlueskyChannel::class];
    }

    public function toBluesky($notifiable): BlueskyPost
    {
        return BlueskyPost::make()
            ->text("Our server {$this->server->name} is down!");
    }
}
```

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/laravel-bluesky-notification-channel.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/laravel-bluesky-notification-channel)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require spatie/laravel-bluesky-notification-channel
```

Add your Bluesky credentials to `config/services.php`:

```php
'bluesky' => [
    'username' => env('BLUESKY_USERNAME'),
    'password' => env('BLUESKY_PASSWORD'),
],
```

## Usage

### Creating posts

You can create a post using the `BlueskyPost` class:

```php
BlueskyPost::make()
    ->text('Hello, Bluesky!')
    ->language('en');
```

### Rich text

The package automatically detects links, mentions (`@handle.bsky.social`) and hashtags (`#topic`) in your post text and converts them to rich text facets.

### Embeds

Link embeds (cards with title, description and thumbnail) are automatically resolved from the first link in your post. You can also specify an embed URL explicitly:

```php
BlueskyPost::make()
    ->text('Check this out!')
    ->embedUrl('https://spatie.be')
```

If you want to disable automatic embed resolution, call `withoutAutomaticEmbeds()`:

```php
BlueskyPost::make()
    ->text('https://spatie.be')
    ->withoutAutomaticEmbeds()
```

### Using the service directly

You can also send posts without using notifications by resolving the `BlueskyService` from the container:

```php
use Spatie\BlueskyNotificationChannel\BlueskyService;

app(BlueskyService::class)->createPost('Hello, Bluesky!');
```

### Customization

You can swap out the default implementations by binding your own classes in a service provider:

```php
use Spatie\BlueskyNotificationChannel\Facets\FacetsResolver;
use Spatie\BlueskyNotificationChannel\Embeds\EmbedResolver;
use Spatie\BlueskyNotificationChannel\IdentityRepository\IdentityRepository;

$this->app->singleton(FacetsResolver::class, MyCustomFacetsResolver::class);
$this->app->singleton(EmbedResolver::class, MyCustomEmbedResolver::class);
$this->app->singleton(IdentityRepository::class, MyCustomIdentityRepository::class);
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

This package is a fork of [innocenzi/bluesky-notification-channel](https://github.com/innocenzi/bluesky-notification-channel) by [Enzo Innocenzi](https://github.com/innocenzi).

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
