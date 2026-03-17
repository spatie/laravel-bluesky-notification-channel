<?php

namespace Spatie\BlueskyNotificationChannel\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\BlueskyNotificationChannel\BlueskyServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            BlueskyServiceProvider::class,
        ];
    }
}
