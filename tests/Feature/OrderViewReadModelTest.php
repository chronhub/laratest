<?php

declare(strict_types=1);

namespace Tests\Unit;

use BankRoute\Model\Order\OrderId;
use BankRoute\Model\Order\OrderState;
use Chronhub\Storm\Clock\PointInTime;
use BankRoute\Model\Customer\CustomerId;
use BankRoute\Projection\ReadModelTable;
use Chronhub\Storm\Contracts\Message\Header;
use BankRoute\Model\Order\Event\OrderCreated;
use BankRoute\Model\Order\Event\OrderItemAdded;
use BankRoute\Projection\Order\OrderViewReadModel;

dataset('orderCreatedEvent', [
    (new OrderCreated([
        'order_id' => OrderId::create()->toString(),
        'customer_id' => CustomerId::create()->toString(),
        'order_status' => OrderState::Pending->value,
        'order_quantity' => 1,
        'product_price' => 100.00,
    ]))->withHeader(Header::EVENT_TIME, (new PointInTime())->nowToString()),
]);

it('create order in database', function (OrderCreated $event) {
    $readModel = new OrderViewReadModel($this->getConnection());

    expect($readModel->isInitialized())->toBeFalse();

    $readModel->initialize();

    expect($readModel->isInitialized())->toBeTrue();

    $readModel->stack('createOrder', $event);
    $readModel->persist();

    $this->assertDatabaseHas(ReadModelTable::ORDER_VIEW, [
        'id' => $event->orderId()->toString(),
        'customer_id' => $event->customerId()->toString(),
        'status' => OrderState::Pending->value,
        'quantity' => 1,
        'price' => 100.00,
        'created_at' => $event->header(Header::EVENT_TIME),
    ]);
})->with('orderCreatedEvent');

it('add order item in database', function (OrderCreated $event) {
    $readModel = new OrderViewReadModel($this->getConnection());
    $readModel->initialize();

    $readModel->stack('createOrder', $event);

    $addItem = (new OrderItemAdded([
        'order_id' => $event->orderId()->toString(),
        'product_quantity' => 1,
        'product_price' => 100.00,
    ]))->withHeader(Header::EVENT_TIME, (new PointInTime())->nowToString());

    $readModel->stack('addOrderItem', $addItem);

    $readModel->persist();

    $this->assertDatabaseHas(ReadModelTable::ORDER_VIEW, [
        'id' => $event->orderId()->toString(),
        'customer_id' => $event->customerId()->toString(),
        'status' => OrderState::Pending->value,
        'quantity' => 2,
        'price' => 200.00,
        'created_at' => $event->header(Header::EVENT_TIME),
    ]);
})->with('orderCreatedEvent');
