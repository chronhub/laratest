<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\CustomerSignUpService;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\Response;

class RegisterCustomerAction
{
    // ab -n 10000 -c 10 http://laratest.dvl.to/customer/register
    public function __invoke(CustomerSignUpService $service): Response
    {
        Artisan::call('order:customer-register', ['--count' => 1]);

        return new Response('Customer registration started');
    }
}
