<?php

declare(strict_types=1);

namespace BankRoute\Model\Order\Event;

use BankRoute\Model\Order\Order;
use BankRoute\Model\Order\OrderId;
use BankRoute\Model\Order\OrderState;
use BankRoute\Model\Customer\CustomerId;
use Chronhub\Storm\Reporter\DomainEvent;
use Chronhub\Storm\Message\HasConstructableContent;
use Chronhub\Storm\Aggregate\Attribute\AsAggregateChanged;

#[AsAggregateChanged(Order::class, 'order_id', ['order_id' => 'string', 'customer_id' => 'string', 'order_status' => 'string'])]
class OrderModified extends DomainEvent
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
}
