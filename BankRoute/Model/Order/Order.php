<?php

declare(strict_types=1);

namespace BankRoute\Model\Order;

use BankRoute\Model\Product\Product;
use BankRoute\Model\Product\ProductId;
use BankRoute\Model\Customer\CustomerId;
use BankRoute\Model\Order\Event\OrderPaid;
use BankRoute\Model\Order\Event\OrderCreated;
use BankRoute\Model\Order\Event\OrderCanceled;
use BankRoute\Model\Order\Event\OrderModified;
use BankRoute\Model\Order\Event\OrderItemAdded;
use BankRoute\Model\Order\Event\OrderItemRemoved;
use BankRoute\Model\Order\Concerns\ApplyOrderEvent;
use Chronhub\Storm\Aggregate\HasAggregateBehaviour;
use Chronhub\Storm\Contracts\Aggregate\AggregateIdentity;
use BankRoute\Model\Order\Event\OrderItemQuantityDecreased;
use BankRoute\Model\Order\Event\OrderItemQuantityIncreased;
use BankRoute\Model\Order\Exceptions\ProductNotFoundInOrder;
use BankRoute\Model\Order\Exceptions\OrderQuantityOutOfBounds;
use BankRoute\Model\Order\Exceptions\UnableToChangeOrderState;
use Chronhub\Storm\Snapshot\ReconstituteAggregateFromSnapshot;
use Chronhub\Storm\Contracts\Aggregate\AggregateRootWithSnapshotting;
use function abs;

final class Order implements AggregateRootWithSnapshotting
{
    use HasAggregateBehaviour;
    use ReconstituteAggregateFromSnapshot;
    use ApplyOrderEvent;

    protected CustomerId $customerId;

    protected OrderState $status;

    protected OrderItems $items;

    final public const DEFAULT_QUANTITY = 0;

    final public const DEFAULT_PRICE = 0.00;

    public static function create(OrderId $orderId, CustomerId $customerId): self
    {
        $self = new self($orderId);
        $self->recordThat(
            OrderCreated::fromContent(
                [
                    'order_id' => $orderId->toString(),
                    'customer_id' => $customerId->toString(),
                    'order_status' => OrderState::Pending->value,
                    'order_quantity' => self::DEFAULT_QUANTITY,
                    'product_price' => self::DEFAULT_PRICE,
                ]
            ));

        return $self;
    }

    public function cancel(): void
    {
        $this->ensureOrderCanBeModified();

        $this->recordThat(OrderCanceled::fromContent(
            [
                'order_id' => $this->orderId()->toString(),
                'customer_id' => $this->customerId()->toString(),
                'order_status' => OrderState::Canceled->value,
                'order_quantity' => self::DEFAULT_QUANTITY,
                'product_price' => self::DEFAULT_PRICE,
                'old_order_quantity' => $this->items->totalQuantity(),
            ]
        ));
    }

    public function addItem(Product $product): void
    {
        $this->ensureOrderCanBeModified();

        $item = new PlusOneItem($this->orderId(), $product->id, $product->price);

        if (! $this->items->hasProduct($item->productId)) {
            $this->recordThat(OrderItemAdded::fromContent($item->jsonSerialize()));
        } else {
            $this->recordThat(OrderItemQuantityIncreased::fromContent($item->jsonSerialize()));
        }

        $this->markAsModified();
    }

    public function decreaseQuantityOfItem(Product $product): void
    {
        $this->ensureOrderCanBeModified();
        $this->ensureProductExists($product->id);

        $item = new MinusOneItem($this->orderId(), $product->id, $product->price);

        $this->ensureQuantityOfProductCanBeDecreased($item);

        if ($this->items->quantityOfProduct($product->id) + $item->quantity === 0) {
            $this->removeItem($product);
        } else {
            $this->recordThat(OrderItemQuantityDecreased::fromContent($item->jsonSerialize()));
        }
    }

    public function removeItem(Product $product): void
    {
        $this->ensureOrderCanBeModified();
        $this->ensureProductExists($product->id);

        $this->recordThat(OrderItemRemoved::fromContent(
            [
                'order_id' => $this->orderId()->toString(),
                'customer_id' => $this->customerId()->toString(),
                'product_id' => $product->id->generate(),
                'product_quantity' => $this->items->quantityOfProduct($product->id),
                'product_price' => $product->price->value,
            ]
        ));
    }

    public function pay(): void
    {
        $this->ensureOrderCanBeModified();

        if ($this->items->totalQuantity() < 1) {
            throw OrderQuantityOutOfBounds::canNotBeEmpty($this->orderId(), $this->status);
        }

        $this->recordThat(OrderPaid::fromContent(
            [
                'order_id' => $this->orderId()->toString(),
                'customer_id' => $this->customerId()->toString(),
                'order_status' => OrderState::Paid->value,
                'order_quantity' => $this->items->totalQuantity(),
            ]
        ));
    }

    private function markAsModified(): void
    {
        if ($this->status === OrderState::Modified) {
            return;
        }

        $this->ensureOrderCanBeModified();

        $this->recordThat(OrderModified::fromContent(
            [
                'order_id' => $this->orderId()->toString(),
                'customer_id' => $this->customerId()->toString(),
                'order_status' => OrderState::Modified->value,
            ]
        ));
    }

    public function orderId(): OrderId|AggregateIdentity
    {
        return $this->aggregateId();
    }

    public function customerId(): CustomerId|AggregateIdentity
    {
        return $this->customerId;
    }

    private function ensureProductExists(ProductId $productId): void
    {
        if (! $this->items->hasProduct($productId)) {
            throw ProductNotFoundInOrder::withProductId($this->orderId(), $productId);
        }
    }

    private function ensureOrderCanBeModified(): void
    {
        if ($this->status !== OrderState::Pending && $this->status !== OrderState::Modified) {
            throw UnableToChangeOrderState::withOrder($this->orderId(), $this->status);
        }
    }

    private function ensureQuantityOfProductCanBeDecreased(MinusOneItem $item): void
    {
        $quantity = $this->items->quantityOfProduct($item->productId);

        if ($quantity === false || $quantity < abs($item->quantity)) {
            throw OrderQuantityOutOfBounds::quantityOfProductCanNotBeDecreased(
                $this->orderId(), $item->productId, $quantity, abs($item->quantity)
            );
        }
    }
}
