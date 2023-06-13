<?php

declare(strict_types=1);

namespace App\Api\Graphql;

use Rebing\GraphQL\Support\Type as GraphqlType;

class ContentType extends GraphqlType
{
    protected $attributes = [
        'name' => 'content',
        'description' => 'event stored content',
    ];

    public function fields(): array
    {
        return [

        ];
    }
}
