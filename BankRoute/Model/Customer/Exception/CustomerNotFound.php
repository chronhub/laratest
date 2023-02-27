<?php

declare(strict_types=1);

namespace BankRoute\Model\Customer\Exception;

use BankRoute\Model\Customer\CustomerId;

final class CustomerNotFound extends CustomerException
{
    public static function withId(CustomerId $customerId): self
    {
        return new self("Customer with id $customerId not found");
    }
}
