<?php

declare(strict_types=1);

namespace App\Console\Commands\App\Seed;

use Illuminate\Console\Command;
use BankRoute\Projection\Customer\CustomerModel;

final class SeedShopCommand extends Command
{
    protected $signature = 'order:seed';

    public function handle(): int
    {
        // todo query model
        $customers = CustomerModel::all();

        $customers->each(function (CustomerModel $customer) {
            $this->callSilent('order:place', ['order' => $customer->getCurrentOrderId()]);
        });

        return self::SUCCESS;
    }
}
