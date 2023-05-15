<?php

declare(strict_types=1);

namespace BankRoute\Model\Order\Query;

use Chronhub\Storm\Reporter\DomainEvent;
use Chronhub\Storm\Message\HasConstructableContent;

class GetOrderById extends DomainEvent
{
    use HasConstructableContent;
}
