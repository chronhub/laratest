<?php

declare(strict_types=1);

namespace App\Testing;

use Closure;
use Countable;
use Traversable;
use IteratorAggregate;
use function is_int;

class RewindableGenerator implements IteratorAggregate, Countable
{
    private Closure $generator;

    private Closure|int $count;

    public function __construct(callable $generator, callable|int $count)
    {
        $this->generator = $generator(...);
        $this->count = is_int($count) ? $count : $count(...);
    }

    public function getIterator(): Traversable
    {
        $generator = $this->generator;

        return $generator();
    }

    public function count(): int
    {
        if (! is_int($count = $this->count)) {
            $this->count = $count();
        }

        return $this->count;
    }
}
