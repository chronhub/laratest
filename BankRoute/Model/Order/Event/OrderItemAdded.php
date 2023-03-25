<?php

declare(strict_types=1);

namespace BankRoute\Model\Order\Event;

use BankRoute\Model\Order\Order;
use BankRoute\Model\Order\OrderId;
use BankRoute\Model\Product\ProductId;
use Chronhub\Storm\Reporter\DomainEvent;
use BankRoute\Model\Product\ProductUnitPrice;
use Chronhub\Storm\Message\HasConstructableContent;
use Chronhub\Storm\Aggregate\Attribute\AsAggregateChanged;

#[AsAggregateChanged(Order::class, 'order_id', ['order_id', 'product_id', 'product_quantity', 'product_price'])]
class OrderItemAdded extends DomainEvent
{
    use HasConstructableContent;

    public function orderId(): OrderId
    {
        return OrderId::fromString($this->content['order_id']);
    }

    public function productId(): ProductId
    {
        return ProductId::fromString($this->content['product_id']);
    }

    public function productQuantity(): int
    {
        return $this->content['product_quantity'];
    }

    public function productPrice(): ProductUnitPrice
    {
        return new ProductUnitPrice($this->content['product_price']);
    }
}
