<?php

declare(strict_types=1);

namespace BankRoute\Model\Product;

final readonly class ProductUnitPrice
{
    public function __construct(public float $value)
    {
    }
}
