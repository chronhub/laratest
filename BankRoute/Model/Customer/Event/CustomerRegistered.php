<?php

declare(strict_types=1);

namespace BankRoute\Model\Customer\Event;

use BankRoute\Model\Customer\CustomerId;
use Chronhub\Storm\Reporter\DomainEvent;
use BankRoute\Model\Customer\CustomerEmail;
use BankRoute\Model\Customer\CustomerStatus;
use Chronhub\Storm\Message\HasConstructableContent;

final class CustomerRegistered extends DomainEvent
{
    use HasConstructableContent;

    public function aggregateId(): CustomerId
    {
        return CustomerId::fromString($this->content['customer_id']);
    }

    public function customerEmail(): CustomerEmail
    {
        return CustomerEmail::fromString($this->content['customer_email']);
    }

    public function customerStatus(): CustomerStatus
    {
        return CustomerStatus::from($this->content['customer_status']);
    }
}
