<?php

declare(strict_types=1);

namespace BankRoute\Model\Customer;

use Chronhub\Storm\Contracts\Aggregate\AggregateRoot;

interface CustomerCollection
{
    public function get(CustomerId $aggregateId): null|Customer|AggregateRoot;

    public function store(Customer $aggregateRoot): void;
}
