<?php

declare(strict_types=1);

namespace BankRoute\Model\Customer;

use RuntimeException;
use Chronhub\Storm\Aggregate\HasAggregateBehaviour;
use BankRoute\Model\Customer\Event\CustomerActivated;
use Chronhub\Storm\Contracts\Aggregate\AggregateRoot;
use BankRoute\Model\Customer\Event\CustomerRegistered;
use Chronhub\Storm\Contracts\Aggregate\AggregateIdentity;

final class Customer implements AggregateRoot
{
    use HasAggregateBehaviour;

    private CustomerEmail $email;

    private CustomerStatus $status;

    public static function register(CustomerId $customerId, CustomerEmail $customerEmail): self
    {
        $self = new self($customerId);

        $self->recordThat(CustomerRegistered::fromContent([
            'customer_id' => $customerId->toString(),
            'customer_email' => $customerEmail->value,
            'customer_status' => CustomerStatus::Registered->value,
        ]));

        return $self;
    }

    public function markAsActivated(): void
    {
        if ($this->status !== CustomerStatus::Registered) {
            throw new RuntimeException('Invalid current customer status');
        }

        $this->recordThat(CustomerActivated::fromContent([
            'customer_id' => $this->aggregateId->toString(),
            'customer_email' => $this->email->value,
            'customer_status' => CustomerStatus::Activated->value,
        ]));
    }

    public function id(): CustomerId|AggregateIdentity
    {
        return $this->aggregateId;
    }

    public function email(): CustomerEmail
    {
        return $this->email;
    }

    public function status(): CustomerStatus
    {
        return $this->status;
    }

    protected function applyCustomerRegistered(CustomerRegistered $event): void
    {
        $this->email = $event->customerEmail();
        $this->status = $event->customerStatus();
    }

    protected function applyCustomerActivated(CustomerActivated $event): void
    {
        $this->status = $event->customerStatus();
    }
}
