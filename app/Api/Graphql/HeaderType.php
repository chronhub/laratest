<?php

declare(strict_types=1);

namespace App\Api\Graphql;

use Rebing\GraphQL\Support\Type as GraphqlType;

class HeaderType extends GraphqlType
{
    protected $attributes = [
        'name' => 'header',
        'description' => 'event stored header',
    ];

    public function fields(): array
    {
        return [

        ];
    }
}
