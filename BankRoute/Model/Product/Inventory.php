<?php

declare(strict_types=1);

namespace BankRoute\Model\Product;

use Countable;
use Illuminate\Support\Collection;
use function explode;

final readonly class Inventory implements Countable
{
    private Collection $products;

    public function __construct()
    {
        $this->products = collect($this->getList())->map(
            fn (array $product) => Product::fromValues(
                $product['id'],
                $product['price'],
                explode('-', $product['id'])[0]
            ));
    }

    public function getProduct(ProductId $productId): ?Product
    {
        if (! $this->hasProduct($productId)) {
            return null;
        }

        return $this->products->first(fn (Product $product) => $product->id->sameValueAs($productId));
    }

    public function hasProduct(ProductId $productId): bool
    {
        return $this->products->contains(fn (Product $product) => $product->id->sameValueAs($productId));
    }

    public function getProducts(): Collection
    {
        return clone $this->products;
    }

    private function getList(): array
    {
        return [
            ['id' => 'ee3acc6c-92c0-4af1-8801-20f801f30b29', 'price' => 12.10],
            ['id' => 'dcc8beb4-8506-4fef-bdd4-395c422289c8', 'price' => 120.44],
            ['id' => '4f24d582-9cb1-4038-bdbe-c9f6a6f93c20', 'price' => 2.12],
            ['id' => '5ae1d2ea-e729-4448-b79e-c7261273b7ff', 'price' => 24.55],
            ['id' => 'caa601ca-2851-4fa3-903c-6ef8ff4e8e01', 'price' => 9.88],
            ['id' => '360f3338-5423-4e06-966a-e88867654c6c', 'price' => 7.10],
            ['id' => '09a789f7-927e-42f3-9599-a410f7c16482', 'price' => 34.40],
            ['id' => '19a789f6-927e-42f3-9599-a410f7c16482', 'price' => 4.41],
            ['id' => '29a789f5-927e-42f3-9599-a410f7c16482', 'price' => 54.40],
            ['id' => '39a789f4-927e-42f3-9599-a410f7c16482', 'price' => 14.70],
            ['id' => '49a789f3-927e-42f3-9599-a410f7c16482', 'price' => 64.20],
            ['id' => '59a789f2-927e-42f3-9599-a410f7c16482', 'price' => 7.60],
            ['id' => '69a789f1-927e-42f3-9599-a410f7c16482', 'price' => 86.56],
            ['id' => '79a789f7-927e-42f3-9599-a410f7c16482', 'price' => 124.40],
            ['id' => '89a789f7-927e-42f3-9599-a410f7c16482', 'price' => 3.80],
        ];
    }

    public function count(): int
    {
        return $this->products->count();
    }
}
