<?php

declare(strict_types=1);

namespace BankRoute\Model\Order;

use BankRoute\Model\Product\ProductId;
use BankRoute\Model\Product\ProductUnitPrice;

final readonly class MinusOneItem extends OrderItem
{
    public int $quantity;

    public function __construct(public OrderId $orderId,
                                public ProductId $productId,
                                public ProductUnitPrice $price)
    {
        $this->quantity = -1;
    }
}
