<?php

declare(strict_types=1);

use Chronhub\Storm\Contracts\Projector\ProjectionOption;

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
        'connection' => 'projector.projection_provider.pgsql',
        //        [
        //            'name' => 'pgsql',
        //            'table' => 'projections',
        //        ],
        'in_memory' => \Chronhub\Storm\Projector\InMemoryProjectionProvider::class,
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
                'options' => 'default',
                'provider' => 'connection',
                'scope' => \Chronhub\Larastorm\Projection\ConnectionQueryScope::class,
            ],

            'emit' => [
                'chronicler' => ['connection', 'read'],
                'options' => 'emit_slow',
                'provider' => 'connection',
                'scope' => \Chronhub\Larastorm\Projection\ConnectionQueryScope::class,
            ],

            'reset' => [
                'chronicler' => ['connection', 'read'],
                'options' => 'reset',
                'provider' => 'connection',
                'scope' => \Chronhub\Larastorm\Projection\ConnectionQueryScope::class,
            ],

            'api_order' => [
                'chronicler' => 'chronicler.api.read',
                'options' => 'lazy',
                'provider' => 'connection',
                'scope' => \App\Api\ApiProjectionQueryScope::class,
            ],
            'api_customer' => [
                'chronicler' => 'chronicler.api.read',
                'options' => 'lazy',
                'provider' => 'connection',
                'scope' => \App\Api\ApiProjectionQueryScope::class,
            ],

        ],

        'in_memory' => [
            'testing' => [
                'chronicler' => ['in_memory', 'standalone'],
                'provider' => 'in_memory',
                'options' => 'in_memory',
                'scope' => \Chronhub\Storm\Projector\InMemoryQueryScope::class,
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

        'default' => [
            ProjectionOption::SIGNAL => true,
            ProjectionOption::LOCKOUT => 1000,
            ProjectionOption::SLEEP => 1000,
            ProjectionOption::TIMEOUT => 1000,
            ProjectionOption::BLOCK_SIZE => 500,
            ProjectionOption::RETRIES => '50, 2000, 50',
            ProjectionOption::DETECTION_WINDOWS => null,
        ],

        'lazy' => [
            ProjectionOption::SIGNAL => true,
            ProjectionOption::LOCKOUT => 100000,
            ProjectionOption::SLEEP => 100000,
            ProjectionOption::TIMEOUT => 100000,
            ProjectionOption::BLOCK_SIZE => 1000,
            ProjectionOption::RETRIES => '50, 2000, 50',
            ProjectionOption::DETECTION_WINDOWS => null,
        ],

        'emit_slow' => [
            ProjectionOption::SIGNAL => true,
            ProjectionOption::LOCKOUT => 500000,
            ProjectionOption::SLEEP => 10000,
            ProjectionOption::TIMEOUT => 10000,
            ProjectionOption::BLOCK_SIZE => 1000,
            ProjectionOption::RETRIES => [],
            ProjectionOption::DETECTION_WINDOWS => null,
        ],

        'reset' => [
            ProjectionOption::SIGNAL => true,
            ProjectionOption::LOCKOUT => 500000,
            ProjectionOption::SLEEP => 0,
            ProjectionOption::TIMEOUT => 0,
            ProjectionOption::BLOCK_SIZE => 1000,
            ProjectionOption::RETRIES => [],
            ProjectionOption::DETECTION_WINDOWS => 'PT1H',
        ],

        'in_memory' => \Chronhub\Storm\Projector\Options\InMemoryProjectionOption::class,

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
            \Chronhub\Larastorm\Support\Console\Generator\MakePersistentProjectionCommand::class,
            \Chronhub\Larastorm\Support\Console\Generator\MakeReadModelProjectionCommand::class,
            \Chronhub\Larastorm\Support\Console\Generator\MakeQueryProjectionCommand::class,
            \Chronhub\Larastorm\Support\Supervisor\Command\SuperviseProjectionCommand::class,
            \Chronhub\Larastorm\Support\Supervisor\Command\CheckSupervisedProjectionStatusCommand::class,
            \Chronhub\Larastorm\Support\Console\Edges\ProjectAllStreamCommand::class,
            \Chronhub\Larastorm\Support\Console\Edges\ProjectStreamCategoryCommand::class,
            \Chronhub\Larastorm\Support\Console\Edges\ProjectMessageNameCommand::class,
        ],
    ],
];
