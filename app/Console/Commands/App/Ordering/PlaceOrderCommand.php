<?php

declare(strict_types=1);

namespace App\Console\Commands\App\Ordering;

use RuntimeException;
use BankRoute\PromiseHandler;
use App\Services\OrderService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use App\Services\QueryOrderService;
use BankRoute\Model\Product\Product;
use BankRoute\Model\Product\GetProducts;
use Chronhub\Larastorm\Support\Facade\Report;
use Symfony\Component\Console\Attribute\AsCommand;
use function rand;
use function count;

#[AsCommand(
    name: 'order:place',
    description: 'Place order by order id'
)]
class PlaceOrderCommand extends Command
{
    use PromiseHandler;

    protected $signature = 'order:place { order : order id }';

    public function handle(QueryOrderService $queryOrder, OrderService $orderService): int
    {
        $order = $queryOrder->getOrderById($this->argument('order'));

        if ($order === null) {
            $this->warn('Order not found');

            return self::FAILURE;
        }

        $this->addOrRemoveItems($order->orderId(), $orderService);

        return self::SUCCESS;
    }

    private function addOrRemoveItems(string $orderId, OrderService $order): void
    {
        $products = [];

        $add = rand(1, 5);

        while ($add !== 0) {
            $product = $this->randomProduct();

            $order->addOrderItem($orderId, $product->id->generate());

            $products[] = [$orderId, $product->id->generate(), $product->price->value, '+1'];

            $add--;
        }

        if (rand(1, 100) > 70) {
            $inProgress = count($products) - 1;

            $index = rand(0, $inProgress);

            $productToRemove = $products[$index];
            [$orderId, $productId, $productPrice ] = $productToRemove;

            $order->decreaseOrderItemQuantity($orderId, $productId);

            $products[] = [$orderId, $productId, $productPrice, '-1'];
        }

        $this->table(['order id', 'product id', 'product price', 'action'], $products);
    }

    private function randomProduct(): Product
    {
        $query = new GetProducts();

        $products = $this->handlePromise(Report::query()->relay($query));

        if (! $products instanceof Collection) {
            throw new RuntimeException('Products not found');
        }

        return $products->shuffle()->first();
    }
}
