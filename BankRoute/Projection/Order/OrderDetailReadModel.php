<?php

declare(strict_types=1);

namespace BankRoute\Projection\Order;

use BankRoute\Projection\ReadModelTable;
use Illuminate\Database\Schema\Blueprint;
use Chronhub\Larastorm\Support\ReadModel\InteractWithBuilder;
use Chronhub\Larastorm\Support\ReadModel\ReadModelConnection;

class OrderDetailReadModel extends ReadModelConnection
{
    use InteractWithBuilder;

    protected function up(): callable
    {
        return function (Blueprint $table): void {
            $table->id();
            $table->uuid('order_id')->index();
            $table->uuid('product_id');
            $table->integer('quantity');
            $table->decimal('price', 8, 2, true);
            $table->timestampTz('created_at', 6);
        };
    }

    protected function tableName(): string
    {
        return ReadModelTable::ORDER_DETAIL;
    }
}
