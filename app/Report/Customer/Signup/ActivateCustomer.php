<?php

declare(strict_types=1);

namespace App\Report\Customer\Signup;

use Chronhub\Storm\Reporter\DomainCommand;
use Chronhub\Storm\Message\HasConstructableContent;

class ActivateCustomer extends DomainCommand
{
    use HasConstructableContent;
}
