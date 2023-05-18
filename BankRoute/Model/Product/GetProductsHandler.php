<?php

declare(strict_types=1);

namespace BankRoute\Model\Product;

use React\Promise\Deferred;

final readonly class GetProductsHandler
{
    public function __construct(private Inventory $inventory)
    {
    }

    public function query(GetProducts $query, Deferred $promise): void
    {
        $promise->resolve($this->inventory->getProducts());
    }
}
