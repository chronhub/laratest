<?php

declare(strict_types=1);

namespace App\Console\Commands\App\Ordering;

use Illuminate\Console\Command;
use App\Services\QueryOrderService;
use BankRoute\Projection\Order\OrderView;
use BankRoute\Projection\Order\OrderDetail;
use Symfony\Component\Console\Helper\Table;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\TableSeparator;

#[AsCommand(
    name: 'order:info',
    description: 'Order info by order id'
)]
final class OrderInfoCommand extends Command
{
    protected $signature = 'order:info { order : order id }';

    public function handle(QueryOrderService $orderQuery): int
    {
        $order = $orderQuery->getOrderByIdWithDetails($this->argument('order'));

        if ($order === null) {
            $this->error('Order not found');

            return self::FAILURE;
        }

        $table = new Table($this->output);
        $table->setHeaders(
            [
                'Order: '.$order->orderId(),
                'Customer: '.$order->customerId(),
                'Total price: '.$order->totalPrice(),
                'Status: '.$order->status(),
            ]);

        $table->setRows([
            [new TableSeparator()],
            ['Product', 'Unit price', 'Total price', 'Quantity'],
            [new TableSeparator()],
            ...$this->getOrderDetails($order),
            [new TableSeparator()],
            ['Total', '', $order->totalPrice(), $order->quantity()],
            [new TableSeparator()],
        ]);

        $table->render();

        return self::SUCCESS;
    }

    private function getOrderDetails(OrderView $order): array
    {
        /** @var OrderDetail|Collection $details */
        $details = $order->getRelation('details');

        return $details
            ->groupBy(fn (OrderDetail $item) => $item->productId())
            ->map(function (Collection $item) {
                $sumQuantity = $item->sum(fn ($item) => $item->quantity());

                /** @var OrderDetail $detail */
                $detail = $item->first();

                return [
                    $detail->productId(),
                    $detail->price(),
                    $detail->price() * $sumQuantity,
                    $sumQuantity,
                ];
            })->toArray();
    }
}
