<?php

declare(strict_types=1);

namespace App\Report\Order;

use Chronhub\Storm\Reporter\DomainCommand;
use Chronhub\Storm\Message\HasConstructableContent;

class DecreaseOrderItemQuantity extends DomainCommand
{
    use HasConstructableContent;

    public function orderId(): string
    {
        return $this->content['order_id'];
    }

    public function productId(): string
    {
        return $this->content['product_id'];
    }
}
