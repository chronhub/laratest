<?php

declare(strict_types=1);

namespace App\Console\Commands\App;

use Throwable;
use Faker\Factory;
use Illuminate\Console\Command;
use Symfony\Component\Uid\Uuid;
use App\Services\CustomerRegistrationService;
use Symfony\Component\Console\Attribute\AsCommand;
use BankRoute\Model\Customer\Exception\CustomerAlreadyExists;

#[AsCommand('bank:customer-register', description: 'Register customer(s)')]
final class RegisterCustomersCommand extends Command
{
    protected $signature = 'bank:customer-register
                            { --num=1 : number of customer to register }';

    public function handle(): int
    {
        $count = $num = (int) $this->option('num');

        $faker = Factory::create();

        $validationFailed = 0;

        /** @var CustomerRegistrationService $service */
        $service = $this->laravel[CustomerRegistrationService::class];

        try {
            while ($num > 0) {
                $id = Uuid::v4()->jsonSerialize();
                $payload = [
                    'id' => $id,
                    'name' => $faker->name(),
                    'email' => $id.'@gmail.com',
                    'password' => $faker->password(8),
                ];

                try {
                    $service->startCustomerRegistration($payload);
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
