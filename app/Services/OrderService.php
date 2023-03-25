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

final readonly class OrderService
{
    public function __construct(private ReportCommand $command, private UniqueId $uniqueId)
    {
    }

    public function createOrder(string $customerId, ?string $orderId = null): void
    {
        $this->command->relay(new StartOrder(
            [
                'order_id' => $orderId ?? $this->uniqueId->generate(),
                'customer_id' => $customerId,
            ]
        ));
    }

    public function cancelOrder(string $orderId, string $customerId): void
    {
        $this->command->relay(new CancelOrder(
            [
                'order_id' => $orderId,
                'customer_id' => $customerId,
            ]
        ));
    }

    public function addOrderItem(string $orderId, $productId): void
    {
        $this->command->relay(new AddOrderItem(
            [
                'order_id' => $orderId,
                'product_id' => $productId,
            ]
        ));
    }

    public function removeOrderItem(string $orderId, string $productId): void
    {
        $this->command->relay(new RemoveOrderItem(
            [
                'order_id' => $orderId,
                'product_id' => $productId,
            ]
        ));
    }

    public function decreaseOrderItemQuantity(string $orderId, string $productId): void
    {
        $this->command->relay(new DecreaseOrderItemQuantity(
            [
                'order_id' => $orderId,
                'product_id' => $productId,
            ]
        ));
    }

    public function payOrder(string $orderId, string $customerId): void
    {
        $this->command->relay(new PayOrder(
            [
                'order_id' => $orderId,
                'customer_id' => $customerId,
            ]
        ));
    }
}
