<?php

declare(strict_types=1);

namespace BankRoute\Projection\Order;

use BankRoute\Model\Order\OrderState;
use BankRoute\Projection\ReadModelTable;
use Illuminate\Database\Schema\Blueprint;
use Chronhub\Larastorm\Support\ReadModel\InteractWithBuilder;
use Chronhub\Larastorm\Support\ReadModel\ReadModelConnection;

class CustomerOrdersReadModel extends ReadModelConnection
{
    use InteractWithBuilder;

    protected function up(): callable
    {
        return function (Blueprint $table): void {
            $table->id();
            $table->uuid('customer_id')->index();
            $table->uuid('order_id')->nullable();
            $table->enum('order_status', OrderState::strings())->nullable();
            $table->timestampsTz(6);
        };
    }

    protected function tableName(): string
    {
        return ReadModelTable::CUSTOMER_ORDERS;
    }
}
