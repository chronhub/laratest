<?php

declare(strict_types=1);

namespace App\Services;

use BankRoute\PromiseHandler;
use Illuminate\Support\Enumerable;
use Chronhub\Storm\Reporter\ReportQuery;
use BankRoute\Projection\Customer\CustomerModel;
use BankRoute\Model\Customer\Query\GetCustomerById;
use BankRoute\Model\Customer\Query\GetRandomCustomersWithLimit;

final readonly class QueryCustomerService
{
    use PromiseHandler;

    public function __construct(private ReportQuery $reportQuery)
    {
    }

    public function getCustomerById(string $customerId): ?CustomerModel
    {
        $query = GetCustomerById::fromContent(['customer_id' => $customerId]);

        return $this->handlePromise($this->reportQuery->relay($query));
    }

    public function getRandomCustomersWithLimit(int $limit): Enumerable
    {
        $query = new GetRandomCustomersWithLimit($limit);

        return $this->handlePromise($this->reportQuery->relay($query));
    }
}
