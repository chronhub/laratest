<?php

declare(strict_types=1);

namespace App\Report\Customer\Signup;

use Chronhub\Storm\Reporter\DomainCommand;
use Chronhub\Storm\Message\HasConstructableContent;
use Chronhub\Storm\Message\Attribute\AsDomainCommand;
use BankRoute\Model\Customer\Handler\RegisterCustomerHandler;

#[AsDomainCommand(
    content: ['customer_id', 'customer_name', 'customer_email'],
    handlers: RegisterCustomerHandler::class
)]
final class RegisterCustomer extends DomainCommand
{
    use HasConstructableContent;
}
