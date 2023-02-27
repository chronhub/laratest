<?php

declare(strict_types=1);

namespace App\Console\Commands\App;

use Throwable;
use Faker\Factory;
use Illuminate\Console\Command;
use Symfony\Component\Uid\Uuid;
use App\Report\RegisterCustomer;
use Chronhub\Larastorm\Support\Facade\Report;
use BankRoute\Model\Customer\Exception\CustomerAlreadyExists;

final class RegisterCustomersCommand extends Command
{
    protected $signature = 'bank:customer-register
                            {--num=1 : number of customer to register}';

    public function handle(): int
    {
        $count = $num = (int) $this->option('num');

        $faker = Factory::create();

        $validationFailed = 0;

        try {
            while ($num > 0) {
                $payload = [
                    'customer_id' => Uuid::v4()->jsonSerialize(),
                    'customer_email' => $faker->unique()->email(),
                ];

                try {
                    Report::command()->relay(new RegisterCustomer($payload));
                } catch (CustomerAlreadyExists) {
                    // only when dispatch sync
                    $validationFailed++;
                }

                $num--;
            }

            $this->info($count.' customer(s) registered');

            if ($validationFailed > 0) {
                $this->line($validationFailed.' customer(s) (at least) failed');
            }
        } catch (Throwable $exception) {
            throw $exception;
        }

        return self::SUCCESS;
    }
}
