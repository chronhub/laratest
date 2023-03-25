<?php

declare(strict_types=1);

namespace App\Report\Order;

use Chronhub\Storm\Reporter\DomainCommand;
use Chronhub\Storm\Message\HasConstructableContent;
use Chronhub\Storm\Message\Attribute\AsDomainCommand;
use BankRoute\Model\Order\Handler\AddOrderItemHandler;

#[AsDomainCommand(['order_id', 'product_id', 'product_price'], 'command', AddOrderItemHandler::class)]
class AddOrderItem extends DomainCommand
{
    use HasConstructableContent;

    public function orderId(): string
    {
        return $this->content['order_id'];
    }

    public function productId(): string
    {
        return $this->content['product_id'];
    }
}
