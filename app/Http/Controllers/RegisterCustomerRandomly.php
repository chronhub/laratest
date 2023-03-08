<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Faker\Factory;
use Symfony\Component\Uid\Uuid;
use App\Services\CustomerRegistrationService;
use Symfony\Component\HttpFoundation\Response;

class RegisterCustomerRandomly
{
    // ab -n 10000 -c 10 http://laratest.dvl.to/customer/register
    public function __invoke(CustomerRegistrationService $service): Response
    {
        $faker = Factory::create();

        $id = Uuid::v4()->jsonSerialize();

        $payload = [
            'id' => $id,
            'name' => $faker->name(),
            'email' => $id.'@gmail.com',
            'password' => $faker->password(8),
        ];

        $service->startCustomerRegistration($payload);

        return new Response('Customer registration started');
    }
}
