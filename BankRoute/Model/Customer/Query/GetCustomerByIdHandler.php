<?php

declare(strict_types=1);

namespace BankRoute\Model\Customer\Query;

use React\Promise\Deferred;
use BankRoute\Projection\Customer\CustomerProvider;

final readonly class GetCustomerByIdHandler
{
    public function __construct(private CustomerProvider $provider)
    {
    }

    public function query(GetCustomerById $query, Deferred $promise): void
    {
        $promise->resolve(
            $this->provider->findById($query->customerId())
        );
    }
}
