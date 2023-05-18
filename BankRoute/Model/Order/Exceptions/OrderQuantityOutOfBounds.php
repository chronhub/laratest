<?php

declare(strict_types=1);

namespace BankRoute\Model\Order\Exceptions;

use BankRoute\Model\Order\OrderId;
use BankRoute\Model\Order\OrderState;
use BankRoute\Model\Product\ProductId;

class OrderQuantityOutOfBounds extends OrderException
{
    public static function canNotBeEmpty(OrderId $orderId, OrderState $orderState): self
    {
        return new self("Order $orderId can not be empty with state $orderState->value");
    }

    public static function quantityOfProductCanNotBeDecreased(OrderId $orderId, ProductId $productId, int $totalQuantity, int $quantityToDecreased): self
    {
        $message = "Product quantity to remove ($quantityToDecreased) is greater than quantity ($totalQuantity)";
        $message .= " of product $productId in order $orderId";

        return new self($message);
    }
}
