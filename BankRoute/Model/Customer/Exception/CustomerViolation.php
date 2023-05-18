<?php

declare(strict_types=1);

namespace BankRoute\Model\Customer\Exception;

use BankRoute\Model\Customer\CustomerId;
use BankRoute\Model\Customer\CustomerStatus;

class CustomerViolation extends CustomerException
{
    public static function unableToActivate(CustomerId $customerId, CustomerStatus $currentStatus): self
    {
        return new self("Unable to activate customer $customerId with status $currentStatus->value");
    }
}
