<?php

declare(strict_types=1);

namespace BankRoute\Model\Customer;

use BankRoute\AggregateIdentityV4;

final class CustomerId extends AggregateIdentityV4
{
    public static function create(): CustomerId
    {
        return self::fromV4();
    }
}
