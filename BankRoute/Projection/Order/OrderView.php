<?php

declare(strict_types=1);

namespace BankRoute\Projection\Order;

use BankRoute\Model\Order\OrderState;
use BankRoute\Projection\ReadModelTable;
use Illuminate\Database\Eloquent\Builder;
use BankRoute\Projection\ReadOnlyEloquentModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderView extends ReadOnlyEloquentModel
{
    protected $table = ReadModelTable::ORDER_VIEW;

    public function orderId(): string
    {
        return $this->getKey();
    }

    public function customerId(): string
    {
        return $this->getAttribute('customer_id');
    }

    public function status(): string
    {
        return $this->getAttribute('status');
    }

    public function quantity(): int
    {
        return $this->getAttribute('quantity');
    }

    public function totalPrice(): float
    {
        return (float) $this->getAttribute('price');
    }

    public function details(): HasMany
    {
        return $this->hasMany(OrderDetail::class, 'order_id', 'id');
    }

    public function scopeOfStatus(Builder $query, string|OrderState ...$orderStatuses): void
    {
        $query->whereIn('status', $orderStatuses, 'or');
    }

    public function scopeModified(Builder $query): void
    {
        $query->where('status', OrderState::Modified);
    }
}
