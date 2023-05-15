<?php

declare(strict_types=1);

namespace BankRoute\Model\Order\Event;

use BankRoute\Model\Order\OrderId;
use BankRoute\Model\Order\OrderState;
use BankRoute\Model\Customer\CustomerId;
use Chronhub\Storm\Reporter\DomainEvent;
use BankRoute\Model\Product\ProductUnitPrice;
use Chronhub\Storm\Message\HasConstructableContent;

final class OrderCanceled extends DomainEvent
{
    use HasConstructableContent;

    public function orderId(): OrderId
    {
        return OrderId::fromString($this->content['order_id']);
    }

    public function customerId(): CustomerId
    {
        return CustomerId::fromString($this->content['customer_id']);
    }

    public function status(): OrderState
    {
        return OrderState::from($this->content['order_status']);
    }

    public function resetQuantity(): int
    {
        return $this->content['order_quantity'];
    }

    public function oldOrderQuantity(): int
    {
        return $this->content['old_order_quantity'];
    }

    public function productPrice(): ProductUnitPrice
    {
        return new ProductUnitPrice($this->content['product_price']);
    }
}
