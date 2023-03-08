<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RegisterCustomerRandomly;

Route::get('/', HomeController::class);
Route::get('/customer/register', RegisterCustomerRandomly::class);
