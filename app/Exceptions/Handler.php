<?php

declare(strict_types=1);

namespace App\Exceptions;

use Throwable;
use BankRoute\Model\Order\Exceptions\OrderNotFound;
use BankRoute\Model\Product\ProductNotFoundInInventory;
use BankRoute\Model\Order\Exceptions\ProductNotFoundInOrder;
use BankRoute\Model\Customer\Exception\CustomerAlreadyExists;
use Chronhub\Storm\Chronicler\Exceptions\ConcurrencyException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        CustomerAlreadyExists::class,
        OrderNotFound::class,
        ProductNotFoundInOrder::class,
        ProductNotFoundInInventory::class,
        ConcurrencyException::class,
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
