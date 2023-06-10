<?php

declare(strict_types=1);

namespace BankRoute\Model\Customer\Query;

final readonly class GetRandomCustomersWithLimit
{
    public function __construct(public int $limit)
    {
    }
}
