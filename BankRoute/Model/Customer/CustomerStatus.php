<?php

declare(strict_types=1);

namespace BankRoute\Model\Customer;

use App\Testing\HasEnumStrings;

enum CustomerStatus: string
{
    use HasEnumStrings;

    case Registered = 'registered';

    case Canceled = 'canceled';

    case Activated = 'activated';

    case Suspended = 'suspended';

    case Closed = 'closed';
}
