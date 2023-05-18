<?php

declare(strict_types=1);

namespace BankRoute\Model\Customer\Handler;

use BankRoute\Model\Customer\CustomerId;
use App\Report\Customer\Signup\ActivateCustomer;
use BankRoute\Model\Customer\CustomerCollection;
use BankRoute\Model\Customer\Exception\CustomerNotFound;

final readonly class ActivateCustomerHandler
{
    public function __construct(private CustomerCollection $customers)
    {
    }

    public function command(ActivateCustomer $command): void
    {
        $accountId = CustomerId::fromString($command->content['customer_id']);

        $customer = $this->customers->get($accountId);

        if ($customer === null) {
            throw CustomerNotFound::withId($accountId);
        }

        $customer->markAsActivated();

        $this->customers->store($customer);
    }
}
