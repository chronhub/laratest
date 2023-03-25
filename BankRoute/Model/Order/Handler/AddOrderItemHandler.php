<?php

declare(strict_types=1);

namespace BankRoute\Model\Order\Handler;

use Exception;
use App\Report\Order\AddOrderItem;
use BankRoute\Model\Order\OrderId;
use BankRoute\Model\Product\Inventory;
use BankRoute\Model\Product\ProductId;
use BankRoute\Model\Order\Service\OrderList;

final readonly class AddOrderItemHandler
{
    public function __construct(private OrderList $orderList,
                                private Inventory $inventory)
    {
    }

    public function command(AddOrderItem $command): void
    {
        $orderId = OrderId::fromString($command->orderId());

        $order = $this->orderList->get($orderId);

        if ($order === null) {
            throw new Exception('Order does not exists');
        }

        if (null === $product = $this->inventory->getProduct(ProductId::fromString($command->productId()))) {
            throw new Exception('Product does not exists');
        }

        $order->addItem($product);

        $this->orderList->store($order);
    }
}
