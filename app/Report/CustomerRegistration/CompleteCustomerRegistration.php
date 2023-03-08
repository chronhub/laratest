<?php

declare(strict_types=1);

namespace App\Report\CustomerRegistration;

use Chronhub\Storm\Reporter\DomainEvent;
use Chronhub\Storm\Message\Attribute\AsDomainEvent;
use Chronhub\Storm\Message\HasConstructableContent;

#[AsDomainEvent(
    content: ['id' => 'string', 'name' => 'string', 'email' => 'string'],
    handlers: CustomerRegistrationCompleted::class
)]
class CompleteCustomerRegistration extends DomainEvent
{
    use HasConstructableContent;
}
