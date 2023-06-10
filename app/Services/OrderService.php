<?php

declare(strict_types=1);

namespace App\Services;

use App\Report\Order\PayOrder;
use App\Report\Order\StartOrder;
use App\Report\Order\CancelOrder;
use App\Report\Order\AddOrderItem;
use App\Report\Order\RemoveOrderItem;
use Chronhub\Storm\Reporter\ReportCommand;
use Chronhub\Storm\Contracts\Message\UniqueId;
use App\Report\Order\DecreaseOrderItemQuantity;
use App\Report\Order\MarkOrderAsProcessingPayment;

final readonly class OrderService
{
    public function __construct(
        private ReportCommand $reporter,
        private UniqueId $uniqueId
    ) {
    }

    public function createOrder(string $customerId, ?string $orderId = null): void
    {
        $command = new StartOrder(
            [
                'order_id' => $orderId ?? $this->uniqueId->generate(),
                'customer_id' => $customerId,
            ]
        );

        $this->reporter->relay($command);
    }

    public function cancelOrder(string $orderId, string $customerId): void
    {
        $command = new CancelOrder(
            [
                'order_id' => $orderId,
                'customer_id' => $customerId,
            ]
        );

        $this->reporter->relay($command);
    }

    public function addOrderItem(string $orderId, $productId): void
    {
        $command = new AddOrderItem(
            [
                'order_id' => $orderId,
                'product_id' => $productId,
            ]
        );

        $this->reporter->relay($command);
    }

    public function removeOrderItem(string $orderId, string $productId): void
    {
        $command = new RemoveOrderItem(
            [
                'order_id' => $orderId,
                'product_id' => $productId,
            ]
        );

        $this->reporter->relay($command);
    }

    public function decreaseOrderItemQuantity(string $orderId, string $productId): void
    {
        $command = new DecreaseOrderItemQuantity(
            [
                'order_id' => $orderId,
                'product_id' => $productId,
            ]
        );

        $this->reporter->relay($command);
    }

    public function payOrder(string $orderId, string $customerId): void
    {
        $command = new PayOrder(
            [
                'order_id' => $orderId,
                'customer_id' => $customerId,
            ]
        );

        $this->reporter->relay($command);
    }

    public function markOrderAsProcessingPayment(string $orderId, string $customerId): void
    {
        $command = new MarkOrderAsProcessingPayment(
            [
                'order_id' => $orderId,
                'customer_id' => $customerId,
            ]
        );

        $this->reporter->relay($command);
    }
}
