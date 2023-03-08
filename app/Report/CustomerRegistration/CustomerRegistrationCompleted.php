<?php

declare(strict_types=1);

namespace App\Report\CustomerRegistration;

use Chronhub\Storm\Message\Attribute\AsHandler;

#[AsHandler(
    domain: CompleteCustomerRegistration::class,
    method: 'onEvent',
)]
class CustomerRegistrationCompleted
{
    public function onEvent(CompleteCustomerRegistration $command): void
    {
        logger()->info('Send activation email to '.$command->content['email']);
    }
}
