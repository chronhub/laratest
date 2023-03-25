<?php

declare(strict_types=1);

namespace BankRoute\Model\Order;

use Chronhub\Storm\Support\HasEnumStrings;

enum OrderState: string
{
    use HasEnumStrings;

    case Pending = 'pending'; // no item

    case Modified = 'modified'; // in progress

    case Canceled = 'canceled';

    case Paid = 'paid'; // order completed
}
