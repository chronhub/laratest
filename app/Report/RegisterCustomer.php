<?php

declare(strict_types=1);

namespace App\Report;

use BankRoute\Model\Customer\CustomerId;
use Chronhub\Storm\Reporter\DomainCommand;
use BankRoute\Model\Customer\CustomerEmail;
use Chronhub\Storm\Message\HasConstructableContent;
use Chronhub\Storm\Contracts\Aggregate\AggregateIdentity;

final class RegisterCustomer extends DomainCommand
{
    use HasConstructableContent;

    public function customerId(): CustomerId|AggregateIdentity
    {
        return CustomerId::fromString($this->toContent()['customer_id']);
    }

    public function email(): CustomerEmail
    {
        return CustomerEmail::fromString($this->toContent()['customer_email']);
    }
}
