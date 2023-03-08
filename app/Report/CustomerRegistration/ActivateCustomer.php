<?php

declare(strict_types=1);

namespace App\Report\CustomerRegistration;

use Chronhub\Storm\Reporter\DomainCommand;
use Chronhub\Storm\Message\HasConstructableContent;
use Chronhub\Storm\Message\Attribute\AsDomainCommand;
use BankRoute\Model\Customer\Handler\ActivateCustomerHandler;

#[AsDomainCommand(
    content: ['customer_id', 'customer_email'],
    handlers: ActivateCustomerHandler::class
)]
class ActivateCustomer extends DomainCommand
{
    use HasConstructableContent;
}
