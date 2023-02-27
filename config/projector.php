<?php

declare(strict_types=1);

use Chronhub\Storm\Contracts\Projector\ProjectorOption;

return [

    'defaults' => [
        'projector' => 'connection',
    ],

    /*
    |--------------------------------------------------------------------------
    | Projection providers
    |--------------------------------------------------------------------------
    |
    */

    'providers' => [
        'eloquent' => \Chronhub\Larastorm\Projection\Projection::class,
        'in_memory' => \Chronhub\Storm\Projector\Provider\InMemoryProjectionProvider::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Projectors
    |--------------------------------------------------------------------------
    |
    | Each projector is bound to an event store
    |
    |       chronicler:     chronicler configuration keys or service registered in ioc
    |       options:        options key
    |       provider:       projection provider key
    |       scope:          projection query scope
    */

    'projectors' => [

        'connection' => [

            'default' => [
                'chronicler' => ['connection', 'write'],
                'options' => 'lazy',
                'provider' => 'eloquent',
                'scope' => \Chronhub\Larastorm\Projection\ConnectionProjectionQueryScope::class,
            ],

            'emit' => [
                'chronicler' => ['connection', 'read'],
                'options' => 'emit_slow',
                'provider' => 'eloquent',
                'scope' => \Chronhub\Larastorm\Projection\ConnectionProjectionQueryScope::class,
            ],

            'reset' => [
                'chronicler' => ['connection', 'read'],
                'options' => 'reset',
                'provider' => 'eloquent',
                'scope' => \Chronhub\Larastorm\Projection\ConnectionProjectionQueryScope::class,
            ],
        ],

        'in_memory' => [
            'testing' => [
                'chronicler' => ['in_memory', 'standalone'],
                'provider' => 'in_memory',
                'options' => 'in_memory',
                'scope' => \Chronhub\Storm\Projector\InMemoryProjectionQueryScope::class,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Projector options
    |--------------------------------------------------------------------------
    |
    | Options can be an array or a string service class/id implementing option contract
    |
    */

    'options' => [

        'default' => [],

        'lazy' => [
            ProjectorOption::SIGNAL => true,
            ProjectorOption::LOCKOUT => 1000000,
            ProjectorOption::SLEEP => 10000,
            ProjectorOption::TIMEOUT => 10000,
            ProjectorOption::BLOCK_SIZE => 1000,
            ProjectorOption::RETRIES => '50, 2000, 50',
            ProjectorOption::DETECTION_WINDOWS => null,
        ],

        'emit_slow' => [
            ProjectorOption::SIGNAL => true,
            ProjectorOption::LOCKOUT => 500000,
            ProjectorOption::SLEEP => 100000,
            ProjectorOption::TIMEOUT => 100000,
            ProjectorOption::BLOCK_SIZE => 1000,
            ProjectorOption::RETRIES => [],
            ProjectorOption::DETECTION_WINDOWS => null,
        ],

        'reset' => [
            ProjectorOption::SIGNAL => true,
            ProjectorOption::LOCKOUT => 500000,
            ProjectorOption::SLEEP => 0,
            ProjectorOption::TIMEOUT => 0,
            ProjectorOption::BLOCK_SIZE => 1000,
            ProjectorOption::RETRIES => [],
            ProjectorOption::DETECTION_WINDOWS => 'PT1H',
        ],

        'in_memory' => \Chronhub\Storm\Projector\Options\InMemoryProjectorOption::class,

        'snapshot' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Console
    |--------------------------------------------------------------------------
    |
    */

    'console' => [

        'load_migrations' => true,

        'commands' => [
            \Chronhub\Larastorm\Support\Console\ReadProjectionCommand::class,
            \Chronhub\Larastorm\Support\Console\WriteProjectionCommand::class,
        ],
    ],
];
