<?php

declare(strict_types=1);

namespace App\Console\Commands\App\Seed;

use Illuminate\Console\Command;
use BankRoute\Projection\Customer\CustomerModel;

final class SeedShopCommand extends Command
{
    protected $signature = 'order:seed {--count=1000}';

    public function handle(): int
    {
        $limit = (int) $this->option('count');

        $customers = CustomerModel::limit($limit)->inRandomOrder()->cursor();

        $customers->each(function (CustomerModel $customer) {
            $this->callSilent('order:place', ['order' => $customer->getCurrentOrderId()]);
        });

        return self::SUCCESS;
    }
}
