<?php

declare(strict_types=1);

namespace BankRoute\Model\Customer\Service;

use BankRoute\Model\Customer\CustomerId;
use BankRoute\Model\Customer\CustomerEmail;

interface UniqueCustomerEmail
{
    public function __invoke(CustomerEmail $email): ?CustomerId;
}
