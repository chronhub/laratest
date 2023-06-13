<?php

declare(strict_types=1);

namespace App\Api\Graphql;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Type as GraphqlType;

class EventStoredType extends GraphqlType
{
    protected $attributes = [
        'name' => 'event_stored',
        'description' => 'event stored',
    ];

    public function fields(): array
    {
        return [
            'aggregate_id' => [
                'type' => Type::string(),
                'description' => 'aggregate id',
            ],

            'aggregate_type' => [
                'type' => Type::string(),
                'description' => 'aggregate type',
            ],

            'aggregate_version' => [
                'type' => Type::int(),
                'description' => 'aggregate version',
            ],

            'content' => [
                'type' => Type::listOf(GraphQL::type('content')),
                'description' => 'event content',
                'is_relation' => false,
            ],

            'headers' => [
                'type' => Type::listOf(GraphQL::type('header')),
                'description' => 'headers event',
                'is_relation' => false,
            ],

            'no' => [
                'type' => Type::int(),
                'description' => 'sequence number',
            ],

            'event_id' => [
                'type' => Type::string(),
                'description' => 'event id',
            ],

            'event_type' => [
                'type' => Type::string(),
                'description' => 'event type',
            ],

            'created_at' => [
                'type' => Type::string(),
                'description' => 'created at',
            ],
        ];
    }
}
