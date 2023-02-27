<?php

declare(strict_types=1);

namespace BankRoute\Infrastructure\Repository;

use BankRoute\Model\Customer\Customer;
use BankRoute\Model\Customer\CustomerId;
use BankRoute\Model\Customer\CustomerCollection;
use Chronhub\Storm\Contracts\Aggregate\AggregateRoot;
use Chronhub\Storm\Contracts\Aggregate\AggregateRepository;

final readonly class CustomerEventStoreRepository implements CustomerCollection
{
    public function __construct(private AggregateRepository $aggregateRepository)
    {
    }

    public function get(CustomerId $aggregateId): null|Customer|AggregateRoot
    {
        return $this->aggregateRepository->retrieve($aggregateId);
    }

    public function store(Customer $aggregateRoot): void
    {
        $this->aggregateRepository->store($aggregateRoot);
    }
}
