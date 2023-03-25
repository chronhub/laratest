<?php

declare(strict_types=1);

namespace BankRoute\Projection\Order;

use Illuminate\Support\Enumerable;
use Illuminate\Database\Eloquent\Model;

final readonly class OrderProvider
{
    public function __construct(private OrderView $model)
    {
    }

    public function orderById(string $orderId): null|OrderView|Model
    {
        return $this->model->newQuery()->find($orderId);
    }

    public function fullOrderById(string $orderId): null|OrderView|Model
    {
        return $this->model
            ->newQuery()
            ->with('details')
            ->where('order_id', $orderId)
            ->first();
    }

    public function fullPendingOrders(): Enumerable
    {
        return $this->model
            ->newQuery()
            ->modified()
            ->has('details')
            ->cursor();
    }

    public function fullOrderByIdAndStatus(string $orderId, string ...$statuses): null|OrderView|Model
    {
        return $this->model
            ->newQuery()
            ->ofStatus(...$statuses)
            ->has('details')
            ->find($orderId);
    }
}
