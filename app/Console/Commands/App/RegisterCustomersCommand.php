<?php

declare(strict_types=1);

namespace App\Console\Commands\App;

use Illuminate\Console\Command;
use App\Models\Factory\CustomerFactory;
use Chronhub\Storm\Reporter\ReportCommand;
use App\Report\Customer\Signup\RegisterCustomer;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('order:customer-register', description: 'Register customer(s)')]
final class RegisterCustomersCommand extends Command
{
    protected $signature = 'order:customer-register { count : Number of customers to register }';

    public function handle(ReportCommand $reporter): int
    {
        $customers = CustomerFactory::create((int) $this->argument('count'));

        foreach ($customers as $customer) {
            $reporter->relay(
                RegisterCustomer::fromContent([
                    'customer_id' => $customer['id'],
                    'customer_name' => $customer['name'],
                    'customer_email' => $customer['email'],
                ])
            );
        }

        $count = $customers->getReturn();

        $this->info("$count customer(s) registered");

        return self::SUCCESS;
    }
}
