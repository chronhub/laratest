<?php

declare(strict_types=1);

namespace App\Report\Customer\Signup;

use Chronhub\Storm\Reporter\DomainCommand;
use Chronhub\Storm\Message\HasConstructableContent;

final class RegisterCustomer extends DomainCommand
{
    use HasConstructableContent;
}
