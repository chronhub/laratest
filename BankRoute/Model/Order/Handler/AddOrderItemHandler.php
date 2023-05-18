<?php

declare(strict_types=1);

namespace BankRoute\Model\Order\Handler;

use App\Report\Order\AddOrderItem;
use BankRoute\Model\Order\OrderId;
use BankRoute\Model\Product\Inventory;
use BankRoute\Model\Product\ProductId;
use BankRoute\Model\Order\Service\OrderList;
use BankRoute\Model\Order\Exceptions\OrderNotFound;
use BankRoute\Model\Product\ProductNotFoundInInventory;

final readonly class AddOrderItemHandler
{
    public function __construct(
        private OrderList $orderList,
        private Inventory $inventory
    ) {
    }

    public function command(AddOrderItem $command): void
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

        $order->addItem($product);

        $this->orderList->store($order);
    }
}
