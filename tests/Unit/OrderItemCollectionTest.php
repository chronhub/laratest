<?php

declare(strict_types=1);

namespace Tests\Unit;

use BankRoute\Model\Order\OrderId;
use BankRoute\Model\Order\OrderItems;
use BankRoute\Model\Order\PlusOneItem;
use BankRoute\Model\Product\ProductId;
use BankRoute\Model\Order\MinusOneItem;
use BankRoute\Model\Product\ProductUnitPrice;

it('create new order items collection', function (ProductId $productId): void {
    $items = new OrderItems();

    expect($items)->toBeInstanceOf(OrderItems::class)->toHaveProperty('items')
        ->and($items->totalQuantity())->toBe(0)
        ->and($items->totalPrice())->toBe(0.00)
        ->and($items->hasProduct($productId))->toBeFalse();
})->with('productId');

it('add new product', function (OrderId $orderId, ProductId $productId): void {
    $items = new OrderItems();

    $productPrice = new ProductUnitPrice(10.00);
    $addItem = new PlusOneItem($orderId, $productId, $productPrice);

    $items->add($addItem);

    expect($items->totalQuantity())->toBe(1)
        ->and($items->totalPrice())->toBe(10.00)
        ->and($items->hasProduct($productId))->toBeTrue();
})->with('orderId', 'productId');

it('remove product', function (OrderId $orderId, ProductId $productId): void {
    $items = new OrderItems();

    $productPrice = new ProductUnitPrice(10.00);
    $addItem = new PlusOneItem($orderId, $productId, $productPrice);

    $items->add($addItem);
    $items->removeProduct($productId);

    expect($items->totalQuantity())->toBe(0)
        ->and($items->totalPrice())->toBe(0.00)
        ->and($items->hasProduct($productId))->toBeFalse();
})->with('orderId', 'productId');

it('increase quantity of product', function (OrderId $orderId, ProductId $productId): void {
    $items = new OrderItems();

    $productPrice = new ProductUnitPrice(10.00);
    $addItem = new PlusOneItem($orderId, $productId, $productPrice);

    $items->add($addItem);
    $items->add($addItem);

    expect($items->totalQuantity())->toBe(2)
        ->and($items->totalPrice())->toBe(20.00)
        ->and($items->hasProduct($productId))->toBeTrue();
})->with('orderId', 'productId');

it('decrease quantity of product', function (OrderId $orderId, ProductId $productId): void {
    $items = new OrderItems();

    $productPrice = new ProductUnitPrice(10.00);
    $addItem = new PlusOneItem($orderId, $productId, $productPrice);

    $items->add($addItem);
    $items->add($addItem);
    $items->add($addItem);

    expect($items->totalQuantity())->toBe(3)
        ->and($items->totalPrice())->toBe(30.00)
        ->and($items->hasProduct($productId))->toBeTrue();

    $removeItem = new MinusOneItem($orderId, $productId, $productPrice);
    $items->decreaseQuantity($removeItem);

    expect($items->totalQuantity())->toBe(2)
        ->and($items->totalPrice())->toBe(20.00)
        ->and($items->hasProduct($productId))->toBeTrue();
})->with('orderId', 'productId');
