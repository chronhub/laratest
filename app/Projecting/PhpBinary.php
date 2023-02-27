<?php

declare(strict_types=1);

namespace App\Projecting;

final class PhpBinary
{
    public static function path(): string
    {
        $escape = '\\' === DIRECTORY_SEPARATOR ? '"' : '\'';

        return $escape.PHP_BINARY.$escape;
    }
}
