<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Unique identifier
    |--------------------------------------------------------------------------
    |
    */

    'unique_id' => \Chronhub\Storm\Message\UniqueIdV4::class,

    /*
    |--------------------------------------------------------------------------
    | Message factory
    |--------------------------------------------------------------------------
    |
    | Message factory is responsible to transform events object|array
    | into a valid Message instance
    |
    | @see \Chronhub\Storm\Reporter\Subscribers\MakeMessage::class
    |
    */

    'factory' => \Chronhub\Storm\Message\MessageFactory::class,

    /*
    |--------------------------------------------------------------------------
    | Message alias
    |--------------------------------------------------------------------------
    |
    */

    'alias' => \Chronhub\Storm\Message\AliasFromClassName::class,

    /*
    |--------------------------------------------------------------------------
    | Message Serializer
    |--------------------------------------------------------------------------
    |
    */

    'serializer' => [
        'normalizers' => [
            \Symfony\Component\Serializer\Normalizer\UidNormalizer::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Message Decorator
    |--------------------------------------------------------------------------
    |
    */

    'decorators' => [
        \Chronhub\Storm\Message\Decorator\EventId::class,
        \Chronhub\Storm\Message\Decorator\EventTime::class,
        \Chronhub\Storm\Message\Decorator\EventType::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Message Subscriber
    |--------------------------------------------------------------------------
    |
    */

    'subscribers' => [
        \Chronhub\Storm\Reporter\Subscribers\MakeMessage::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Console
    |--------------------------------------------------------------------------
    |
    */

    'console' => [

        'commands' => [
            \Chronhub\Larastorm\Support\Console\ListMessagerSubscribersCommand::class,
        ],
    ],
];
