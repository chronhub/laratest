<?php

declare(strict_types=1);

namespace App\Console\Commands\App;

use Illuminate\Console\Command;
use Symfony\Component\Uid\Uuid;
use App\Services\CustomerSignUpService;
use Chronhub\Larastorm\Support\Facade\Report;
use App\Report\Customer\Signup\RegisterCustomer;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('order:customer-register', description: 'Register customer')]
final class RegisterCustomersCommand extends Command
{
    protected $signature = 'order:customer-register { --count=1 }';

    public function handle(CustomerSignUpService $signUpService): int
    {
        $count = $num = (int) $this->option('count');

        while ($num != 0) {
            $factory = $this->factory();

            Report::command()->relay(
                RegisterCustomer::fromContent([
                    'customer_id' => $factory['id'],
                    'customer_name' => $factory['name'],
                    'customer_email' => $factory['email'],
                ])
            );
            $num--;
        }

        $info = $count > 1 ? "$count Customers" : 'Customer with id '.$factory['id'];

        $this->info($info.' registered');

        return self::SUCCESS;

        $count = $num = (int) $this->option('count');

        while ($num != 0) {
            $customerId = $this->signUp($signUpService);
            $num--;
        }

        $info = $count > 1 ? "$count Customers" : 'Customer with id '.$customerId;

        $this->info($info.' registered');

        return self::SUCCESS;
    }

    private function signUp(CustomerSignUpService $signUpService): ?string
    {
        $payload = $this->factory();

        $signUpService->start($payload);

        return $payload['id'];
    }

    private function factory(): array
    {
        $id = Uuid::v4()->jsonSerialize();

        return [
            'id' => $id,
            'name' => fake()->name(),
            'email' => $id.'@gmail.com',
            'password' => fake()->password(8),
        ];
    }
}
