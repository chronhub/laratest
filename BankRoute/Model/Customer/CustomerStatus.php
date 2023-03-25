<?php

declare(strict_types=1);

namespace BankRoute\Model\Customer;

use Chronhub\Storm\Support\HasEnumStrings;

enum CustomerStatus: string
{
    use HasEnumStrings;

    case Registered = 'registered';

    case Canceled = 'canceled';

    case Activated = 'activated';

    case Suspended = 'suspended';

    case Closed = 'closed';
}
