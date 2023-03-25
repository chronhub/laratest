<?php

declare(strict_types=1);

namespace BankRoute\Projection;

final class ReadModelTable
{
    final public const CUSTOMER = 'read_customer';

    final public const ORDER_VIEW = 'read_order_view';

    final public const ORDER_DETAIL = 'read_order_detail';

    final public const CUSTOMER_ORDERS = 'read_customer_orders';
}
