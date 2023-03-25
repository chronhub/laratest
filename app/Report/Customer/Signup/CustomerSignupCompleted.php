<?php

declare(strict_types=1);

namespace App\Report\Customer\Signup;

use Chronhub\Storm\Reporter\DomainEvent;
use Chronhub\Storm\Message\Attribute\AsDomainEvent;
use Chronhub\Storm\Message\HasConstructableContent;

#[AsDomainEvent(
    content: ['id' => 'string', 'name' => 'string', 'email' => 'string'],
    handlers: SendActivationEmailOnSignUpCompleted::class
)]
class CustomerSignupCompleted extends DomainEvent
{
    use HasConstructableContent;
}