<?php

declare(strict_types=1);

namespace BankRoute\Projection\Order;

use BankRoute\Model\Order\OrderState;
use BankRoute\Projection\ReadModelTable;
use Illuminate\Database\Schema\Blueprint;
use Chronhub\Larastorm\Support\ReadModel\InteractWithBuilder;
use Chronhub\Larastorm\Support\ReadModel\ReadModelConnection;

class OrderViewReadModel extends ReadModelConnection
{
    use InteractWithBuilder;

    protected function up(): callable
    {
        return function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('customer_id')->index();
            $table->enum('status', OrderState::strings());
            $table->integer('quantity')->default(0);
            $table->decimal('price', 8, 2, true)->default(0.00);
            $table->timestampsTz(6);
        };
    }

    protected function tableName(): string
    {
        return ReadModelTable::ORDER_VIEW;
    }
}
