<?php

declare(strict_types=1);

namespace App\Report\Order;

use Chronhub\Storm\Reporter\DomainCommand;
use Chronhub\Storm\Contracts\Message\AsyncMessage;
use Chronhub\Storm\Message\HasConstructableContent;
use BankRoute\Model\Order\Handler\StartOrderHandler;
use Chronhub\Storm\Message\Attribute\AsDomainCommand;

#[AsDomainCommand(['order_id', 'customer_id'], 'command', StartOrderHandler::class)]
class StartOrder extends DomainCommand implements AsyncMessage
{
    use HasConstructableContent;

    public function orderId(): string
    {
        return $this->content['order_id'];
    }

    public function customerId(): string
    {
        return $this->content['customer_id'];
    }
}
