<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PlaceOrderAction;
use App\Http\Controllers\RegisterCustomerAction;

Route::get('/', HomeController::class);
Route::get('/customer/register', RegisterCustomerAction::class);
Route::get('/order/place', PlaceOrderAction::class);
