<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use function serialize;
use function unserialize;

final class HomeController
{
    public function __invoke(): string
    {
        $data = null;

        $serialized = serialize($data); // string(18) "a:1:{s:3:"foo";s:3:"bar";}"

        dd(unserialize($serialized)); // string(18) "a:1:{s:3:"foo";s:3:"bar";}"

        return 'ok';
    }
}
