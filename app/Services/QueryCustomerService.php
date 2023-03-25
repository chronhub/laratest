<?php

declare(strict_types=1);

namespace App\Services;

use BankRoute\PromiseHandler;
use Chronhub\Storm\Reporter\ReportQuery;
use Chronhub\Larastorm\Support\Facade\Report;
use BankRoute\Projection\Customer\CustomerModel;
use BankRoute\Model\Customer\Query\GetCustomerById;

final readonly class QueryCustomerService
{
    use PromiseHandler;

    public function __construct(private ReportQuery $reportQuery)
    {
    }

    public function getCustomerById(string $customerId): ?CustomerModel
    {
        $query = GetCustomerById::fromContent(['customer_id' => $customerId]);

        return $this->handlePromise(Report::query()->relay($query));
    }
}
