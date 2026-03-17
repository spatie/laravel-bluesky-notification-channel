<?php

namespace Spatie\BlueskyNotificationChannel;

use Illuminate\Config\Repository as Config;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Http\Client\Factory as HttpClient;
use Spatie\BlueskyNotificationChannel\Embeds\EmbedResolver;
use Spatie\BlueskyNotificationChannel\Embeds\LinkEmbedResolverUsingCardyb;
use Spatie\BlueskyNotificationChannel\Facets\DefaultFacetsResolver;
use Spatie\BlueskyNotificationChannel\Facets\FacetsResolver;
use Spatie\BlueskyNotificationChannel\IdentityRepository\IdentityRepository;
use Spatie\BlueskyNotificationChannel\IdentityRepository\IdentityRepositoryUsingCache;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class BlueskyServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('laravel-bluesky-notification-channel');
    }

    public function packageBooted(): void
    {
        $this->app->singleton(FacetsResolver::class, fn () => new DefaultFacetsResolver);
        $this->app->singleton(EmbedResolver::class, fn () => new LinkEmbedResolverUsingCardyb);

        $this->app->singleton(IdentityRepository::class, fn () => new IdentityRepositoryUsingCache(
            cache: $this->app->make(Cache::class),
            key: $this->app->make(Config::class)->get('services.bluesky.identity_cache_key', default: IdentityRepositoryUsingCache::DEFAULT_CACHE_KEY),
        ));

        $this->app->singleton(BlueskyClient::class, fn () => new BlueskyClient(
            httpClient: $this->app->make(HttpClient::class),
            embedResolver: $this->app->make(EmbedResolver::class),
            baseUrl: $this->app->make(Config::class)->get('services.bluesky.base_url', default: BlueskyClient::DEFAULT_BASE_URL),
            username: $this->app->make(Config::class)->get('services.bluesky.username'),
            password: $this->app->make(Config::class)->get('services.bluesky.password'),
        ));

        $this->app->singleton(SessionManager::class, fn () => new SessionManager(
            client: $this->app->make(BlueskyClient::class),
            identityRepository: $this->app->make(IdentityRepository::class),
        ));

        $this->app->singleton(BlueskyService::class, fn () => new BlueskyService(
            client: $this->app->make(BlueskyClient::class),
            identityRepository: $this->app->make(IdentityRepository::class),
            sessionManager: $this->app->make(SessionManager::class),
            embedResolver: $this->app->make(EmbedResolver::class),
            facetsResolver: $this->app->make(FacetsResolver::class),
        ));
    }
}
