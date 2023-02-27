<?php

declare(strict_types=1);

namespace BankRoute\Model\Customer\Handler;

use App\Report\RegisterCustomer;
use BankRoute\Model\Customer\Customer;
use BankRoute\Model\Customer\CustomerId;
use BankRoute\Model\Customer\CustomerCollection;
use BankRoute\Model\Customer\Service\UniqueCustomerEmail;
use BankRoute\Model\Customer\Exception\CustomerAlreadyExists;

final class RegisterCustomerHandler
{
    public function __construct(private readonly CustomerCollection $customers,
                                private readonly UniqueCustomerEmail $uniqueCustomerEmail)
    {
    }

    public function command(RegisterCustomer $command): void
    {
        $accountId = $command->customerId();

        if (null !== $this->customers->get($accountId)) {
            throw CustomerAlreadyExists::withId($accountId);
        }

        $email = $command->email();

        $otherAccountId = ($this->uniqueCustomerEmail)($email);

        if ($otherAccountId instanceof CustomerId) {
            throw CustomerAlreadyExists::withEmail($email);
        }

        $customer = Customer::register($accountId, $email);

        $this->customers->store($customer);
    }
}
