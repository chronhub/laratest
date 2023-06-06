<?php

declare(strict_types=1);

namespace Tests\Feature;

use BankRoute\Model\Order\Order;
use BankRoute\Model\Order\OrderId;
use BankRoute\Model\Product\Product;
use BankRoute\Model\Order\OrderState;
use BankRoute\Model\Product\ProductId;
use BankRoute\Model\Customer\CustomerId;
use BankRoute\Model\Order\Event\OrderCreated;
use BankRoute\Model\Order\Event\OrderModified;
use BankRoute\Model\Order\Event\OrderItemAdded;

it('create a new order aggregate', function (OrderId $orderId, CustomerId $customerId): void {
    $order = Order::create($orderId, $customerId);

    expect($orderId)->toEqual($order->orderId())
        ->and($customerId)->toEqual($order->customerId())
        ->and($order->state())->toEqual(OrderState::Pending)
        ->and($order->version())->toEqual(1);

    $releaseEvents = $order->releaseEvents();

    expect($releaseEvents)->toHaveCount(1)
        ->and($releaseEvents[0])->toBeInstanceOf(OrderCreated::class)
        ->and($releaseEvents[0]->orderId())->toEqual($orderId)
        ->and($releaseEvents[0]->customerId())->toEqual($customerId);
})->with('orderId', 'customerId');

it('add new product to order', function (OrderId $orderId, CustomerId $customerId) {
    $order = Order::create($orderId, $customerId);
    $productId = ProductId::create();
    $product = Product::fromValues($productId->generate(), 10.00, 'product name');

    expect($order->state())->toEqual(OrderState::Pending);

    $order->addItem($product);

    expect($order->state())->toEqual(OrderState::Modified);

    $releaseEvents = $order->releaseEvents();

    expect($releaseEvents)->toHaveCount(3)
        ->and($releaseEvents[1])->toBeInstanceOf(OrderItemAdded::class)
        ->and($releaseEvents[1]->orderId())->toEqual($orderId)
        ->and($releaseEvents[1]->productId())->toEqual($productId)
        ->and($releaseEvents[1]->productPrice()->value)->toEqual(10.00)
        ->and($releaseEvents[2])->toBeInstanceOf(OrderModified::class)
        ->and($releaseEvents[2]->orderId())->toEqual($orderId)
        ->and($releaseEvents[2]->customerId())->toEqual($customerId)
        ->and($releaseEvents[2]->status())->toEqual(OrderState::Modified);
})->with('orderId', 'customerId');
