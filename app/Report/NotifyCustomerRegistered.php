<?php

declare(strict_types=1);

namespace App\Report;

use BankRoute\Model\Customer\Event\CustomerRegistered;

final class NotifyCustomerRegistered
{
    public function onEvent(CustomerRegistered $event): void
    {
//        logger('Customer registered', [
//            'id' => $event->aggregateId()->toString(),
//            'email' => $event->customerEmail()->value,
//        ]);
    }
}
