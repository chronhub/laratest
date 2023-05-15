<?php

declare(strict_types=1);

return [

    'repository' => [

        'use_messager_decorators' => true,

        'event_decorators' => [],

        'repositories' => [

            'customer' => [
                'chronicler' => \Chronhub\Storm\Contracts\Chronicler\Chronicler::class,
                'strategy' => 'single',
                'aggregate_type' => \BankRoute\Model\Customer\Customer::class,
                'cache' => [
                    'size' => 50,
                    'driver' => 'redis',
                ],
                'event_decorators' => [],
            ],

            'order' => [
                'chronicler' => \Chronhub\Storm\Contracts\Chronicler\Chronicler::class,
                'strategy' => 'single',
                'aggregate_type' => \BankRoute\Model\Order\Order::class,
                'cache' => [
                    'size' => 50,
                    'driver' => 'redis',
                ],
                'event_decorators' => [],
                //'use_snapshot' => 'connection',
            ],
        ],
    ],
];
