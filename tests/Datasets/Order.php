<?php

declare(strict_types=1);

use BankRoute\Model\Order\OrderId;
use BankRoute\Model\Customer\CustomerId;

dataset('orderId', [OrderId::create()]);
dataset('customerId', [CustomerId::create()]);
