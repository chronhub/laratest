<?php

declare(strict_types=1);

namespace App\Report\Order;

use Chronhub\Storm\Reporter\DomainCommand;
use Chronhub\Storm\Message\HasConstructableContent;
use BankRoute\Model\Order\Handler\CancelOrderHandler;
use Chronhub\Storm\Message\Attribute\AsDomainCommand;

#[AsDomainCommand(['order_id', 'customer_id'], 'command', CancelOrderHandler::class)]
final class CancelOrder extends DomainCommand
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
