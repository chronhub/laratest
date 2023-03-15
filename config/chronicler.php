<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Domain event serializer
    |--------------------------------------------------------------------------
    |
    */

    'event_serializer' => [
        'normalizers' => [
            \Symfony\Component\Serializer\Normalizer\UidNormalizer::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Event store
    |--------------------------------------------------------------------------
    |
    */

    'defaults' => [
        'provider' => 'connection',
        'event_stream_provider' => [],
        'providers' => [
            'connection' => \Chronhub\Larastorm\EventStore\EventStoreConnectionProvider::class,
            'in_memory' => \Chronhub\Storm\Chronicler\InMemory\InMemoryChroniclerProvider::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Chronicler providers
    |--------------------------------------------------------------------------
    |
    |   query loader: (optional : default cursor)
    |       cursor: CursorQueryLoader (default if null or key "query_loader" is missing )
    |       lazy: LazyQueryLoader or lazy:1000 to configure chunkSize
    |       or your own string service id
    |
    |   strategy: (required)
    |       single: single stream persistence
    |       single_indexed: single stream persistence (only for mysql)
    |       per_aggregate: per aggregate stream persistence
    |
    |   write_lock:
    |       true: use default write lock depends on driver
    |       false: a fake write lock
    |       string: your own service
    |
    |   is_transactional:
    |       only required when you need a standalone (not eventable so no stream tracker) chronicler instance
    |
    */

    'providers' => [

        /**
         * Connection
         *
         * available pgsql and mysql with default mysql, mariadb, percona engines
         * by now, there is no optimization for any connection (queries/filter/scope, tables, databases)
         */
        'connection' => [
            'write' => [
                'store' => 'pgsql',
                'tracking' => [
                    'tracker_id' => \Chronhub\Storm\Chronicler\TrackTransactionalStream::class,
                    'subscribers' => [
                        \Chronhub\Storm\Publisher\EventPublisherSubscriber::class,
                        \Chronhub\Storm\Support\Bridge\MakeCausationDomainCommand::class,
                    ],
                ],
                'write_lock' => true,
                'strategy' => \Chronhub\Larastorm\EventStore\Persistence\PgsqlSingleStreamPersistence::class,
                'query_loader' => 'cursor',
            ],

            'read' => [
                'store' => 'pgsql',
                'is_transactional' => false,
                'write_lock' => false,
                'strategy' => \Chronhub\Larastorm\EventStore\Persistence\PgsqlSingleStreamPersistence::class,
                'query_loader' => 'cursor',
            ],
        ],

        /**
         * In memory
         *
         * In memory driver keys are predefined
         * If you need your own in memory instance
         *      extend the manager
         *      or make your own chronicler provider
         */
        'in_memory' => [

            'standalone' => [],

            'transactional' => [],

            'eventable' => [
                'tracking' => [
                    'tracker_id' => \Chronhub\Storm\Chronicler\TrackStream::class,
                    'subscribers' => [],
                ],
            ],

            'transactional_eventable' => [
                'tracking' => [
                    'tracker_id' => \Chronhub\Storm\Chronicler\TrackTransactionalStream::class,
                    'subscribers' => [],
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Migration and command
    |--------------------------------------------------------------------------
    |
    */

    'console' => [
        'load_migration' => true,

        'commands' => [
            \Chronhub\Larastorm\Support\Console\CreateEventStreamCommand::class,
        ],
    ],
];
