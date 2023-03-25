<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use BankRoute\Model\Order\OrderId;
use BankRoute\Model\Order\OrderItems;
use BankRoute\Model\Order\PlusOneItem;
use BankRoute\Model\Order\MinusOneItem;

class OrderItemCollectionTest extends TestCase
{
    public function testQuantityOfProduct(): void
    {
        $items = new OrderItems();
        $this->assertFalse($items->hasProduct('1'));

        $orderId = OrderId::create();
        $productId = '1';

        $items->add(new PlusOneItem($orderId, $productId, 1.00));
        $items->add(new PlusOneItem($orderId, $productId, 1.00));
        $items->add(new PlusOneItem($orderId, $productId, 1.00));
        $items->add(new PlusOneItem($orderId, $productId, 1.00));

        $this->assertEquals(4, $items->quantityOfProduct($productId));
        $this->assertEquals(4.0, $items->totalPrice());
    }

    public function testRemoveProduct(): void
    {
        $items = new OrderItems();
        $this->assertEquals(0, $items->quantityOfProduct('0'));
        $this->assertFalse($items->hasProduct('1'));
        $this->assertFalse($items->hasProduct('2'));

        $orderId = OrderId::create();
        $product1 = '1';
        $product2 = '2';

        $items->add(new PlusOneItem($orderId, $product1, 1.00));
        $items->add(new PlusOneItem($orderId, $product1, 1.00));
        $items->decreaseQuantity(new MinusOneItem($orderId, $product1, 1.00));
        $items->decreaseQuantity(new MinusOneItem($orderId, $product1, 1.00));
        $items->add(new PlusOneItem($orderId, $product2, 10.00));
        $items->add(new PlusOneItem($orderId, $product2, 10.00));
        $items->decreaseQuantity(new MinusOneItem($orderId, $product2, 10.00));

        $this->assertTrue($items->hasProduct('1'));
        $this->assertTrue($items->hasProduct('2'));

        $this->assertEquals(0, $items->quantityOfProduct($product1));
        $this->assertEquals(1, $items->quantityOfProduct($product2));

        $this->assertEquals(10.00, $items->totalPrice());
        $this->assertEquals(1, $items->totalQuantity());
    }

    public function testRemoveProductWhenUpdateQuantity(): void
    {
        $items = new OrderItems();
        $this->assertEquals(0, $items->quantityOfProduct('0'));

        $orderId = OrderId::create();
        $productId = '1';

        $items->add(new PlusOneItem($orderId, $productId, 1.00));
        $items->add(new PlusOneItem($orderId, $productId, 1.00));

        $this->assertEquals(2, $items->quantityOfProduct('1'));

        $items->decreaseQuantity(new MinusOneItem($orderId, $productId, 1.00));
        $this->assertEquals(1, $items->quantityOfProduct('1'));

        $items->decreaseQuantity(new MinusOneItem($orderId, $productId, 1.00));
        $this->assertEquals(0, $items->quantityOfProduct('1'));

        $items->decreaseQuantity(new MinusOneItem($orderId, $productId, 1.00));
        $this->assertEquals(false, $items->quantityOfProduct('1'));
    }
}
