<?php

declare(strict_types=1);

namespace BankRoute\Projection\Customer;

use Illuminate\Support\LazyCollection;
use Illuminate\Database\Eloquent\Model;

final readonly class CustomerProvider
{
    public function __construct(private CustomerModel $model)
    {
    }

    public function findById(string $customerId): null|CustomerModel|Model
    {
        return $this->model->newQuery()->find($customerId);
    }

    public function findByEmail(string $customerEmail): null|CustomerModel|Model
    {
        return $this->model->newQuery()->where('email', $customerEmail)->first();
    }

    public function findAll(): LazyCollection
    {
        return $this->model->newQuery()->cursor();
    }
}