<?php

declare(strict_types=1);

namespace App\Report\CustomerRegistration;

use Chronhub\Storm\Reporter\DomainCommand;
use Chronhub\Storm\Contracts\Message\AsyncMessage;
use Chronhub\Storm\Message\HasConstructableContent;
use Chronhub\Storm\Message\Attribute\AsDomainCommand;
use BankRoute\Model\Customer\Handler\RegisterCustomerHandler;

#[AsDomainCommand(
    content: ['customer_id', 'customer_name', 'customer_email'],
    handlers: RegisterCustomerHandler::class
)]
final class RegisterCustomer extends DomainCommand implements AsyncMessage
{
    use HasConstructableContent;
}
