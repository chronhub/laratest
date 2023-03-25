<?php

declare(strict_types=1);

namespace App\Report\Customer\Signup;

use Chronhub\Storm\Reporter\DomainEvent;
use Chronhub\Storm\Contracts\Message\AsyncMessage;
use Chronhub\Storm\Message\HasConstructableContent;
use BankRoute\ProcessManager\CustomerRegistrationProcess;
use Chronhub\Storm\Message\Attribute\AsNotificationEvent;

#[AsNotificationEvent(
    content: ['id', 'name', 'email', 'password'],
    handlers: CustomerRegistrationProcess::class
)]
final class CustomerSignupStarted extends DomainEvent implements AsyncMessage
{
    use HasConstructableContent;
}
