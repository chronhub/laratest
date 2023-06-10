<?php

declare(strict_types=1);

namespace BankRoute\Projection\Order;

use Illuminate\Support\Enumerable;
use BankRoute\Model\Order\OrderState;
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
            ->find($orderId);
    }

    public function modifiedOrdersForPayment(): Enumerable
    {
        return $this->model
            ->newQuery()
            ->where('quantity', '>', 0)
            ->where('status', OrderState::Modified)
            ->cursor();
    }

    public function fullPreparedForPaymentOrders(): Enumerable
    {
        return $this->model
            ->newQuery()
            ->where('status', OrderState::ProcessingPayment)
            ->with('details')
            ->whereHas('details')
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
