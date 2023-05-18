<?php

declare(strict_types=1);

namespace BankRoute\Model\Product;

class ProductNotFoundInInventory extends ProductException
{
    public static function withProductId(ProductId $productId): self
    {
        return new self("Product $productId not found in inventory");
    }
}
