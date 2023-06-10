<?php

declare(strict_types=1);

namespace BankRoute\Model\Customer\Query;

use React\Promise\Deferred;
use BankRoute\Projection\Customer\CustomerProvider;

final readonly class GetRandomCustomersWithLimitHandler
{
    public function __construct(private CustomerProvider $customerProvider)
    {
    }

    public function query(GetRandomCustomersWithLimit $query, Deferred $promise): void
    {
        $promise->resolve($this->customerProvider->getRandomCustomersWithLimit($query->limit));
    }
}
