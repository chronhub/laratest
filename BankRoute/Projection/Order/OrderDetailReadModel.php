<?php

declare(strict_types=1);

namespace BankRoute\Projection\Order;

use BankRoute\Projection\ReadModelTable;
use Illuminate\Database\Schema\Blueprint;
use Chronhub\Larastorm\Support\Facade\Clock;
use Chronhub\Storm\Contracts\Message\Header;
use BankRoute\Model\Order\Event\OrderCanceled;
use BankRoute\Model\Order\Event\OrderItemAdded;
use BankRoute\Model\Order\Event\OrderItemRemoved;
use BankRoute\Model\Order\Event\OrderItemQuantityDecreased;
use BankRoute\Model\Order\Event\OrderItemQuantityIncreased;
use Chronhub\Larastorm\Support\ReadModel\AbstractQueryModelConnection;

class OrderDetailReadModel extends AbstractQueryModelConnection
{
    protected function recordOrderItem(OrderItemAdded|OrderItemQuantityIncreased|OrderItemQuantityDecreased $event): void
    {
        $this->insert(
            [
                'order_id' => $event->orderId()->toString(),
                'product_id' => $event->productId(),
                'quantity' => $event->productQuantity(),
                'price' => $event->productPrice()->value,
                'created_at' => Clock::format($event->header(Header::EVENT_TIME)),
            ]
        );
    }

    protected function removeOrderItem(OrderItemRemoved $event): void
    {
        $this->query()
            ->where('order_id', $event->orderId()->toString())
            ->where('product_id', $event->productId())
            ->delete();
    }

    protected function cancelOrder(OrderCanceled $event): void
    {
        $this->query()->where('order_id', $event->orderId()->toString())->delete();
    }

    protected function up(): callable
    {
        return function (Blueprint $table): void {
            $table->id();
            $table->uuid('order_id')->index();
            $table->uuid('product_id');
            $table->integer('quantity');
            $table->decimal('price', 8, 2, true);
            $table->timestampTz('created_at', 6);
        };
    }

    protected function tableName(): string
    {
        return ReadModelTable::ORDER_DETAIL;
    }
}
