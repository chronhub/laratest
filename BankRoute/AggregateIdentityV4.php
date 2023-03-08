<?php

declare(strict_types=1);

namespace BankRoute;

use Symfony\Component\Uid\Uuid;
use Chronhub\Storm\Aggregate\HasAggregateIdentity;
use Chronhub\Storm\Contracts\Aggregate\AggregateIdentity;

abstract class AggregateIdentityV4 implements AggregateIdentity
{
    use HasAggregateIdentity;

    protected static function fromV4(): static
    {
        return new static(Uuid::v4());
    }

    abstract public static function create(): AggregateIdentity;
}
