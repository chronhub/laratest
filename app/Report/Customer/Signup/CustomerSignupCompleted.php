<?php

declare(strict_types=1);

namespace App\Report\Customer\Signup;

use Chronhub\Storm\Reporter\DomainEvent;
use Chronhub\Storm\Message\HasConstructableContent;

class CustomerSignupCompleted extends DomainEvent
{
    use HasConstructableContent;
}
