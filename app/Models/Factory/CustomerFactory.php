<?php

declare(strict_types=1);

namespace App\Models\Factory;

use Generator;
use Symfony\Component\Uid\Uuid;

final class CustomerFactory
{
    /**
     * @return Generator{
     *     array{id: string, name: string, email: string, password: string}
     * }
     */
    public static function create(int $count = 1): Generator
    {
        $count = $num = $count;

        while ($count !== 0) {
            $id = Uuid::v4()->jsonSerialize();

            yield [
                'id' => $id,
                'name' => fake()->name(),
                'email' => $id.'@gmail.com',
                'password' => fake()->password(8),
            ];
            $count--;
        }

        return $num;
    }
}
