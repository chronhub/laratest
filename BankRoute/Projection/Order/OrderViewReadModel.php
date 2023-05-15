<?php

declare(strict_types=1);

namespace BankRoute\Projection\Order;

use BankRoute\Model\Order\OrderState;
use BankRoute\Projection\ReadModelTable;
use Illuminate\Database\Schema\Blueprint;
use BankRoute\Model\Order\Event\OrderPaid;
use Chronhub\Larastorm\Support\Facade\Clock;
use Chronhub\Storm\Contracts\Message\Header;
use BankRoute\Model\Order\Event\OrderCreated;
use BankRoute\Model\Order\Event\OrderCanceled;
use BankRoute\Model\Order\Event\OrderModified;
use BankRoute\Model\Order\Event\OrderItemAdded;
use BankRoute\Model\Order\Event\OrderItemRemoved;
use BankRoute\Model\Order\Event\OrderItemQuantityDecreased;
use BankRoute\Model\Order\Event\OrderItemQuantityIncreased;
use Chronhub\Larastorm\Support\ReadModel\AbstractQueryModelConnection;
use function abs;

class OrderViewReadModel extends AbstractQueryModelConnection
{
    protected function createOrder(OrderCreated $event): void
    {
        $this->insert([
            'id' => $event->orderId()->toString(),
            'customer_id' => $event->customerId()->toString(),
            'status' => $event->status()->value,
            'quantity' => $event->orderQuantity(),
            'price' => $event->productPrice()->value,
            'created_at' => Clock::format($event->header(Header::EVENT_TIME)),
        ]);
    }

    protected function addOrderItem(OrderItemAdded $event): void
    {
        $this->incrementEach($event->orderId()->toString(), [
            'quantity' => abs($event->productQuantity()),
            'price' => $event->productPrice()->value,
        ], ['updated_at' => Clock::format($event->header(Header::EVENT_TIME))]);
    }

    protected function removeOrderItem(OrderItemRemoved $event): void
    {
        $this->decrementEach($event->orderId()->toString(), [
            'quantity' => abs($event->productQuantity()),
            'price' => $event->productPrice()->value * abs($event->productQuantity()),
        ], ['updated_at' => Clock::format($event->header(Header::EVENT_TIME))]);
    }

    protected function increaseOrderQuantity(OrderItemQuantityIncreased $event): void
    {
        $this->incrementEach($event->orderId()->toString(), [
            'quantity' => abs($event->productQuantity()),
            'price' => $event->productPrice()->value,
        ], ['updated_at' => Clock::format($event->header(Header::EVENT_TIME))]);
    }

    protected function decreaseOrderQuantity(OrderItemQuantityDecreased $event): void
    {
        $this->decrementEach($event->orderId()->toString(), [
            'quantity' => abs($event->productQuantity()),
            'price' => $event->productPrice()->value,
        ], ['updated_at' => Clock::format($event->header(Header::EVENT_TIME))]);
    }

    protected function updateOrderStatus(OrderCanceled|OrderModified|OrderPaid $event): void
    {
        $this->update($event->orderId()->toString(), [
            'status' => $event->status()->value,
            'updated_at' => Clock::format($event->header(Header::EVENT_TIME)),
        ]);
    }

    protected function up(): callable
    {
        return function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('customer_id')->index();
            $table->enum('status', OrderState::strings());
            $table->integer('quantity')->default(0);
            $table->decimal('price', 8, 2, true)->default(0.00);
            $table->timestampsTz(6);
        };
    }

    protected function tableName(): string
    {
        return ReadModelTable::ORDER_VIEW;
    }
}
