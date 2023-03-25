<?php

declare(strict_types=1);

namespace BankRoute\Model\Customer\Handler;

use BankRoute\Model\Customer\Customer;
use BankRoute\Model\Customer\CustomerId;
use BankRoute\Model\Customer\CustomerEmail;
use Chronhub\Storm\Message\Attribute\AsHandler;
use App\Report\Customer\Signup\RegisterCustomer;
use BankRoute\Model\Customer\CustomerCollection;
use BankRoute\Model\Customer\Service\UniqueCustomerEmail;
use BankRoute\Model\Customer\Exception\CustomerAlreadyExists;

#[AsHandler(
    domain: RegisterCustomer::class,
    method: 'command',
)]
final readonly class RegisterCustomerHandler
{
    public function __construct(private CustomerCollection $customers,
                                private UniqueCustomerEmail $uniqueCustomerEmail)
    {
    }

    public function command(RegisterCustomer $command): void
    {
        $accountId = CustomerId::fromString($command->content['customer_id']);

        if (null !== $this->customers->get($accountId)) {
            throw CustomerAlreadyExists::withId($accountId);
        }

        $email = CustomerEmail::fromString($command->content['customer_email']);

        $otherAccountId = ($this->uniqueCustomerEmail)($email);

        if ($otherAccountId instanceof CustomerId) {
            throw CustomerAlreadyExists::withEmail($email);
        }

        $customer = Customer::register($accountId, $email);

        $this->customers->store($customer);
    }
}
