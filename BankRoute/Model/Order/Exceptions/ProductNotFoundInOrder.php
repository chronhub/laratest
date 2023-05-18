<?php

declare(strict_types=1);

namespace BankRoute\Model\Order\Exceptions;

use BankRoute\Model\Order\OrderId;
use BankRoute\Model\Product\ProductId;

class ProductNotFoundInOrder extends OrderException
{
    public static function withProductId(OrderId $orderId, ProductId $productId): self
    {
        return new self("Product with id $productId not found in order $orderId");
    }
}
