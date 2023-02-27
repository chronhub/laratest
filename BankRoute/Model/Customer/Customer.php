<?php

declare(strict_types=1);

namespace BankRoute\Model\Customer;

use Chronhub\Storm\Aggregate\HasAggregateBehaviour;
use Chronhub\Storm\Contracts\Aggregate\AggregateRoot;
use BankRoute\Model\Customer\Event\CustomerRegistered;
use Chronhub\Storm\Contracts\Aggregate\AggregateIdentity;

final class Customer implements AggregateRoot
{
    use HasAggregateBehaviour;

    private CustomerEmail $email;

    public static function register(CustomerId $customerId, CustomerEmail $customerEmail): self
    {
        $self = new self($customerId);

        $self->recordThat(CustomerRegistered::fromContent([
            'customer_id' => $customerId->toString(),
            'customer_email' => $customerEmail->value,
        ]));

        return $self;
    }

    public function id(): CustomerId|AggregateIdentity
    {
        return $this->aggregateId;
    }

    public function email(): CustomerEmail
    {
        return $this->email;
    }

    protected function applyCustomerRegistered(CustomerRegistered $event): void
    {
        $this->email = $event->customerEmail();
    }
}
