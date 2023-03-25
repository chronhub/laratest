<?php

declare(strict_types=1);

namespace BankRoute\Model\Order\Query;

use Chronhub\Storm\Reporter\DomainEvent;
use Chronhub\Storm\Message\Attribute\AsDomainQuery;
use Chronhub\Storm\Message\HasConstructableContent;

#[AsDomainQuery(content: ['order_id'], targetMethod:'query', handlers: GetOrderByIdHandler::class)]
class GetOrderById extends DomainEvent
{
    use HasConstructableContent;
}
