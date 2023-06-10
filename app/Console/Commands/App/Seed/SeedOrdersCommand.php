<?php

declare(strict_types=1);

namespace App\Console\Commands\App\Seed;

use Illuminate\Console\Command;
use App\Services\QueryCustomerService;
use BankRoute\Projection\Customer\CustomerModel;

final class SeedOrdersCommand extends Command
{
    protected $signature = 'order:seed { count=1000 : Number of orders to seed }';

    public function handle(QueryCustomerService $service): int
    {
        $limit = (int) $this->argument('count');

        $customers = $service->getRandomCustomersWithLimit($limit);

        $customers->each(
            fn (CustomerModel $customer) => $this->callSilent(
                'order:place',
                ['order' => $customer->getCurrentOrderId()]
            )
        );

        $this->info("Seeded {$limit} orders for {$customers->count()} customers");

        return self::SUCCESS;
    }
}
