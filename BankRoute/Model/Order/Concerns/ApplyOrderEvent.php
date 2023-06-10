<?php

declare(strict_types=1);

namespace BankRoute\Model\Order\Concerns;

use BankRoute\Model\Order\OrderItems;
use BankRoute\Model\Order\PlusOneItem;
use BankRoute\Model\Order\MinusOneItem;
use BankRoute\Model\Order\Event\OrderPaid;
use BankRoute\Model\Order\Event\OrderCreated;
use BankRoute\Model\Order\Event\OrderCanceled;
use BankRoute\Model\Order\Event\OrderModified;
use BankRoute\Model\Order\Event\OrderItemAdded;
use BankRoute\Model\Order\Event\OrderItemRemoved;
use BankRoute\Model\Order\Event\OrderItemQuantityDecreased;
use BankRoute\Model\Order\Event\OrderItemQuantityIncreased;
use BankRoute\Model\Order\Event\OrderMarkedAsProcessingPayment;

trait ApplyOrderEvent
{
    protected function applyOrderCreated(OrderCreated $event): void
    {
        $this->customerId = $event->customerId();
        $this->status = $event->status();
        $this->items = new OrderItems();
    }

    protected function applyOrderCanceled(OrderCanceled $event): void
    {
        $this->status = $event->status();
        $this->items = new OrderItems();
    }

    protected function applyOrderItemAdded(OrderItemAdded $event): void
    {
        $this->items->add(
            new PlusOneItem($event->orderId(), $event->productId(), $event->productPrice())
        );
    }

    protected function applyOrderItemRemoved(OrderItemRemoved $event): void
    {
        $this->items->removeProduct($event->productId());
    }

    protected function applyOrderModified(OrderModified $event): void
    {
        $this->status = $event->status();
    }

    protected function applyOrderItemQuantityIncreased(OrderItemQuantityIncreased $event): void
    {
        $this->items->add(
            new PlusOneItem($event->orderId(), $event->productId(), $event->productPrice())
        );
    }

    protected function applyOrderItemQuantityDecreased(OrderItemQuantityDecreased $event): void
    {
        $this->items->decreaseQuantity(
            new MinusOneItem($event->orderId(), $event->productId(), $event->productPrice())
        );
    }

    protected function applyOrderMarkedAsProcessingPayment(OrderMarkedAsProcessingPayment $event): void
    {
        $this->status = $event->status();
    }

    protected function applyOrderPaid(OrderPaid $event): void
    {
        $this->status = $event->status();
    }
}
