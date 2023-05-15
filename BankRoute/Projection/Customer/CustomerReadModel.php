<?php

declare(strict_types=1);

namespace BankRoute\Projection\Customer;

use BankRoute\Projection\ReadModelTable;
use Illuminate\Database\Schema\Blueprint;
use BankRoute\Model\Customer\CustomerStatus;
use Chronhub\Larastorm\Support\Facade\Clock;
use Chronhub\Storm\Contracts\Message\Header;
use BankRoute\Model\Order\Event\OrderCreated;
use BankRoute\Model\Customer\Event\CustomerRegistered;
use Chronhub\Larastorm\Support\ReadModel\AbstractQueryModelConnection;

final class CustomerReadModel extends AbstractQueryModelConnection
{
    protected function recordCustomer(CustomerRegistered $event): void
    {
        $this->insert(
            [
                $this->getKey() => $event->aggregateId()->toString(),
                'email' => $event->customerEmail()->value,
                'status' => $event->customerStatus()->value,
                'created_at' => Clock::format($event->header(Header::EVENT_TIME)),
            ]
        );
    }

    protected function updateCustomerOrder(OrderCreated $event): void
    {
        $this->update($event->customerId()->toString(), ['current_order_id' => $event->orderId()->toString()]);
    }

    protected function up(): callable
    {
        return function (Blueprint $table): void {
            $table->uuid($this->getKey())->primary();
            $table->string('email')->index();
            $table->enum('status', CustomerStatus::strings());
            $table->uuid('current_order_id')->nullable();
            $table->timestampsTz(6);
        };
    }

    protected function tableName(): string
    {
        return ReadModelTable::CUSTOMER;
    }
}
