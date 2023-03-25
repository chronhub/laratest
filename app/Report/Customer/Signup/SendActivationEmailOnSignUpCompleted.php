<?php

declare(strict_types=1);

namespace App\Report\Customer\Signup;

use Chronhub\Storm\Message\Attribute\AsHandler;

#[AsHandler(
    domain: CustomerSignupCompleted::class,
    method: 'onEvent',
)]
class SendActivationEmailOnSignUpCompleted
{
    public function onEvent(CustomerSignupCompleted $command): void
    {
        logger()->info('Send activation email to '.$command->content['email']);
    }
}
