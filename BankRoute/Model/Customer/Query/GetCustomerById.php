<?php

declare(strict_types=1);

namespace BankRoute\Model\Customer\Query;

use Chronhub\Storm\Reporter\DomainEvent;
use Chronhub\Storm\Message\Attribute\AsDomainQuery;
use Chronhub\Storm\Message\HasConstructableContent;

#[AsDomainQuery(['customer_id' => 'string'], 'query', GetCustomerByIdHandler::class)]
class GetCustomerById extends DomainEvent
{
    use HasConstructableContent;

    public function customerId(): string
    {
        return $this->content['customer_id'];
    }
}
