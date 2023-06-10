<?php

declare(strict_types=1);

namespace App\Report\Order;

use Chronhub\Storm\Reporter\DomainCommand;
use Chronhub\Storm\Message\HasConstructableContent;

class MarkOrderAsProcessingPayment extends DomainCommand
{
    use HasConstructableContent;

    public function orderId(): string
    {
        return $this->content['order_id'];
    }

    public function customerId(): string
    {
        return $this->content['customer_id'];
    }
}
