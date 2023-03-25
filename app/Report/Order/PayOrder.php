<?php

declare(strict_types=1);

namespace App\Report\Order;

use Chronhub\Storm\Reporter\DomainCommand;
use BankRoute\Model\Order\Handler\PayOrderHandler;
use Chronhub\Storm\Message\HasConstructableContent;
use Chronhub\Storm\Message\Attribute\AsDomainCommand;

#[AsDomainCommand(['order_id', 'customer_id'], 'command', PayOrderHandler::class)]
final class PayOrder extends DomainCommand
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
