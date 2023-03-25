<?php

declare(strict_types=1);

namespace BankRoute\Projection\Order;

use BankRoute\Projection\ReadModelTable;
use BankRoute\Projection\ReadOnlyEloquentModel;

class OrderDetail extends ReadOnlyEloquentModel
{
    protected $table = ReadModelTable::ORDER_DETAIL;

    public function orderId(): string
    {
        return $this->getAttribute('order_id');
    }

    public function productId(): string
    {
        return $this->getAttribute('product_id');
    }

    public function quantity(): int
    {
        return $this->getAttribute('quantity');
    }

    public function price(): float
    {
        return (float) $this->getAttribute('price');
    }
}
