<?php

declare(strict_types=1);

namespace App\Report\Order;

use Chronhub\Storm\Reporter\DomainCommand;
use Chronhub\Storm\Contracts\Message\AsyncMessage;
use Chronhub\Storm\Message\HasConstructableContent;

class StartOrder extends DomainCommand implements AsyncMessage
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
