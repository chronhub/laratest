<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\Response;

class PlaceOrderAction
{
    public function __invoke(): Response
    {
        Artisan::call('order:seed', ['count' => 1]);

        return new Response('Order placed');
    }
}
