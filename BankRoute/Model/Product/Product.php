<?php

declare(strict_types=1);

namespace BankRoute\Model\Product;

use JsonSerializable;

final readonly class Product implements JsonSerializable
{
    public function __construct(
        public ProductId $id,
        public ProductUnitPrice $price,
        public ProductName $name)
    {
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id->generate(),
            'price' => $this->price->value,
            'name' => $this->name->value,
        ];
    }

    public static function fromValues(string $productId, float $price, string $name): self
    {
        return new self(
            ProductId::fromString($productId),
            new ProductUnitPrice($price),
            new ProductName($name)
        );
    }
}
