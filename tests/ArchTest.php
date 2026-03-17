<?php

arch('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->each->not->toBeUsed();

arch('exceptions extend BlueskyException')
    ->expect('Spatie\BlueskyNotificationChannel\Exceptions')
    ->toExtend('Spatie\BlueskyNotificationChannel\Exceptions\BlueskyException')
    ->ignoring('Spatie\BlueskyNotificationChannel\Exceptions\BlueskyException');

arch('all source classes use strict types or are in the correct namespace')
    ->expect('Spatie\BlueskyNotificationChannel')
    ->toBeClasses()
    ->ignoring([
        'Spatie\BlueskyNotificationChannel\Embeds\EmbedResolver',
        'Spatie\BlueskyNotificationChannel\Facets\FacetsResolver',
        'Spatie\BlueskyNotificationChannel\IdentityRepository\IdentityRepository',
        'Spatie\BlueskyNotificationChannel\Support\SerializesToLexiconObject',
        'Spatie\BlueskyNotificationChannel\Support\IgnoreProperty',
    ]);

arch('interfaces are in the right places')
    ->expect('Spatie\BlueskyNotificationChannel\Embeds\EmbedResolver')
    ->toBeInterface();

arch('identity repository interface')
    ->expect('Spatie\BlueskyNotificationChannel\IdentityRepository\IdentityRepository')
    ->toBeInterface();

arch('facets resolver interface')
    ->expect('Spatie\BlueskyNotificationChannel\Facets\FacetsResolver')
    ->toBeInterface();
