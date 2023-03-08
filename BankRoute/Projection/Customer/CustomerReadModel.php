<?php

declare(strict_types=1);

namespace BankRoute\Projection\Customer;

use BankRoute\Projection\ReadModelTable;
use Illuminate\Database\Schema\Blueprint;
use BankRoute\Model\Customer\CustomerStatus;
use Chronhub\Larastorm\Support\ReadModel\InteractWithBuilder;
use Chronhub\Larastorm\Support\ReadModel\ReadModelConnection;

final class CustomerReadModel extends ReadModelConnection
{
    use InteractWithBuilder;

    protected function up(): callable
    {
        return function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('email')->index();
            $table->enum('status', CustomerStatus::strings());
            $table->timestampsTz(6);
        };
    }

    protected function tableName(): string
    {
        return ReadModelTable::CUSTOMER;
    }
}
