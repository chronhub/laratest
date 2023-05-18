<?php

declare(strict_types=1);

namespace BankRoute\Model\Order\Handler;

use BankRoute\Model\Order\OrderId;
use BankRoute\Model\Product\Inventory;
use BankRoute\Model\Product\ProductId;
use BankRoute\Model\Order\Service\OrderList;
use App\Report\Order\DecreaseOrderItemQuantity;
use BankRoute\Model\Order\Exceptions\OrderNotFound;
use BankRoute\Model\Product\ProductNotFoundInInventory;

final readonly class DecreaseOrderItemQuantityHandler
{
    public function __construct(
        private OrderList $orderList,
        private Inventory $inventory
    ) {
    }

    public function command(DecreaseOrderItemQuantity $command): void
    {
        $orderId = OrderId::fromString($command->orderId());

        $order = $this->orderList->get($orderId);

        if ($order === null) {
            throw OrderNotFound::withOrderId($orderId);
        }

        $productId = ProductId::fromString($command->productId());
        $product = $this->inventory->getProduct($productId);

        if ($product === null) {
            throw ProductNotFoundInInventory::withProductId($productId);
        }

        $order->decreaseQuantityOfItem($product);

        $this->orderList->store($order);
    }
}
