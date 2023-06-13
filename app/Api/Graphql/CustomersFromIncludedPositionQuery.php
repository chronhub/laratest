<?php

declare(strict_types=1);

namespace App\Api\Graphql;

use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Facades\GraphQL;
use GraphQL\Type\Definition\Type as GraphQLType;

class CustomersFromIncludedPositionQuery extends Query
{
    //?query=query+CustomersFromIncludedPosition($from:Int, $limit:Int){user(id:$id){id,email}}&variables={"id":123}
    protected $attributes = [
        'name' => 'customers_from_included_position',
        'description' => 'customers from included position',
    ];

    public function args(): array
    {
        return [
            'from' => [
                'name' => 'from',
                'type' => GraphQLType::int(),
                'rules' => ['required', 'integer', 'min:1'],
            ],

            'limit' => [
                'name' => 'limit',
                'type' => GraphQLType::int(),
                'rules' => ['required', 'integer', 'min:1'],
            ],
        ];
    }

    public function type(): GraphQLType
    {
        return GraphQLType::listOf(GraphQL::type('event_stored'));
    }
}
