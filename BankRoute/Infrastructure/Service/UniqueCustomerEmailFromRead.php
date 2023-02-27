<?php

declare(strict_types=1);

namespace BankRoute\Infrastructure\Service;

use BankRoute\Model\Customer\CustomerId;
use BankRoute\Model\Customer\CustomerEmail;
use BankRoute\Projection\Customer\CustomerModel;
use BankRoute\Projection\Customer\CustomerProvider;
use BankRoute\Model\Customer\Service\UniqueCustomerEmail;

final readonly class UniqueCustomerEmailFromRead implements UniqueCustomerEmail
{
    public function __construct(private CustomerProvider $customerProvider)
    {
    }

    public function __invoke(CustomerEmail $email): ?CustomerId
    {
        $customer = $this->customerProvider->findByEmail($email->value);

        if ($customer instanceof CustomerModel) {
            return CustomerId::fromString($customer->getKey());
        }

        return null;
    }
}
