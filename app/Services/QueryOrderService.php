<?php

declare(strict_types=1);

namespace App\Services;

use BankRoute\PromiseHandler;
use Illuminate\Support\Enumerable;
use Chronhub\Storm\Reporter\ReportQuery;
use BankRoute\Projection\Order\OrderView;
use BankRoute\Model\Order\Query\GetOrderById;
use BankRoute\Model\Order\Query\GetFullOrderById;
use BankRoute\Model\Order\Query\GetFullPendingOrder;
use BankRoute\Model\Order\Query\GetModifiedOrdersForPayment;
use BankRoute\Model\Order\Query\GetFullPreparedForPaymentOrders;

final readonly class QueryOrderService
{
    use PromiseHandler;

    public function __construct(private ReportQuery $reporter)
    {
    }

    public function getOrderById(string $orderId): ?OrderView
    {
        $query = GetOrderById::fromContent(['order_id' => $orderId]);

        return $this->handlePromise($this->reporter->relay($query));
    }

    public function getOrderByIdWithDetails(string $orderId): ?OrderView
    {
        $query = GetFullOrderById::fromContent(['order_id' => $orderId]);

        return $this->handlePromise($this->reporter->relay($query));
    }

    public function getPendingOrderByIdWithDetails(string $orderId): ?OrderView
    {
        $query = GetFullPendingOrder::fromContent(['order_id' => $orderId]);

        return $this->handlePromise($this->reporter->relay($query));
    }

    public function getPreparedOrdersForPaymentWithDetails(): Enumerable
    {
        $query = new GetFullPreparedForPaymentOrders();

        return $this->handlePromise($this->reporter->relay($query));
    }

    public function getModifiedOrdersForPayment(): Enumerable
    {
        $query = new GetModifiedOrdersForPayment();

        return $this->handlePromise($this->reporter->relay($query));
    }
}
