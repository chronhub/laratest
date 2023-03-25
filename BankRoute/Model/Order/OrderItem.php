<?php

declare(strict_types=1);

namespace BankRoute\Model\Order;

use JsonSerializable;

abstract readonly class OrderItem implements JsonSerializable
{
    public function jsonSerialize(): array
    {
        return [
            'order_id' => $this->orderId->toString(),
            'product_id' => $this->productId->generate(),
            'product_quantity' => $this->quantity,
            'product_price' => $this->price->value,
        ];
    }
}
