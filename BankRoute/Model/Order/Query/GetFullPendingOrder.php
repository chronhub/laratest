<?php

declare(strict_types=1);

namespace BankRoute\Model\Order\Query;

use Chronhub\Storm\Reporter\DomainQuery;
use Chronhub\Storm\Message\HasConstructableContent;

final class GetFullPendingOrder extends DomainQuery
{
    use HasConstructableContent;
}
