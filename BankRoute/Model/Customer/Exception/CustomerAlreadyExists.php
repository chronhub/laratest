<?php

declare(strict_types=1);

namespace BankRoute\Model\Customer\Exception;

use BankRoute\Model\Customer\CustomerId;
use BankRoute\Model\Customer\CustomerEmail;

final class CustomerAlreadyExists extends CustomerException
{
    public static function withId(CustomerId $customerId): self
    {
        return new self("Customer with id $customerId already exist");
    }

    public static function withEmail(CustomerEmail $email): self
    {
        return new self("Customer with email $email->value already exist");
    }
}
