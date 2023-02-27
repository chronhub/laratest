<?php

declare(strict_types=1);

namespace BankRoute;

use Symfony\Component\Uid\Uuid;
use Chronhub\Storm\Aggregate\HasAggregateIdentity;
use Chronhub\Storm\Contracts\Aggregate\AggregateIdentity;

abstract class AggregateIdentityV4 implements AggregateIdentity
{
    use HasAggregateIdentity;

    public static function create(): self
    {
        return new static(Uuid::v4());
    }
}
