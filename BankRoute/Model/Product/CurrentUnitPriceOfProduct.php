<?php

declare(strict_types=1);

namespace BankRoute\Model\Product;

final readonly class CurrentUnitPriceOfProduct
{
    public function __construct(private Inventory $inventory)
    {
    }

    public function unitPriceOfProduct(string $productId): ?ProductUnitPrice
    {
        return $this->inventory->getProduct(ProductId::fromString($productId))?->price;
    }
}
