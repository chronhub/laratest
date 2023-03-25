<?php

declare(strict_types=1);

namespace BankRoute\Model\Order\Query;

use Chronhub\Storm\Reporter\DomainQuery;
use Chronhub\Storm\Message\Attribute\AsDomainQuery;
use Chronhub\Storm\Message\HasConstructableContent;

#[AsDomainQuery(content: ['order_id', 'order_status'], targetMethod:'query', handlers: GetFullPendingOrderHandler::class)]
final class GetFullPendingOrder extends DomainQuery
{
    use HasConstructableContent;
}
