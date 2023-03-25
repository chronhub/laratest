<?php

declare(strict_types=1);

namespace BankRoute\Model\Order;

use Illuminate\Support\Collection;
use BankRoute\Model\Product\ProductId;

final class OrderItems
{
    private Collection $items;

    public function __construct()
    {
        $this->items = new Collection();
    }

    public function add(PlusOneItem $item): void
    {
        $this->items->add($item);
    }

    public function decreaseQuantity(MinusOneItem $item): void
    {
        $this->items->add($item);
    }

    public function removeProduct(ProductId $productId): void
    {
        $this->items = $this->items->reject(fn (OrderItem $product) => $product->productId->sameValueAs($productId));
    }

    public function quantityOfProduct(ProductId $productId): int|false
    {
        if (! $this->hasProduct($productId)) {
            return false;
        }

        $quantity = (int) $this->items
            ->filter(fn (OrderItem $item) => $item->productId->sameValueAs($productId))
            ->reduce(fn (int $total, OrderItem $item) => $total + $item->quantity, 0);

        return $quantity < 0 ? false : $quantity;
    }

    public function hasProduct(ProductId $productId): bool
    {
        return $this->items->filter(fn (OrderItem $item) => $item->productId->sameValueAs($productId))->isNotEmpty();
    }

    public function totalPrice(): float
    {
        $total = $this->items->sum(fn (OrderItem $item) => $item->quantity * $item->price->value);

        return (float) $total;
    }

    public function totalQuantity(): int
    {
        return (int) $this->items->reduce(fn (int $total, OrderItem $item) => $total + $item->quantity, 0);
    }
}
