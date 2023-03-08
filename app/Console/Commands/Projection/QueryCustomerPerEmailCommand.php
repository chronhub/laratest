<?php

declare(strict_types=1);

namespace App\Console\Commands\Projection;

use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Chronhub\Larastorm\Support\Facade\Project;
use Chronhub\Storm\Contracts\Chronicler\QueryFilter;
use BankRoute\Model\Customer\Event\CustomerRegistered;
use Chronhub\Storm\Contracts\Projector\ReadModelProjectorCaster;

final class QueryCustomerPerEmailCommand extends Command
{
    protected $signature = 'bank:query-customer-per-email { email : customer email }';

    public function handle(): int
    {
        $email = $this->argument('email');

        $query = Project::create('emit')->projectQuery();

        $query
            ->initialize(fn (): array => ['found' => false])
            ->fromStreams('customer')
            ->whenAny(function (CustomerRegistered $event, array $state) use ($email): array {
                /** @var ReadModelProjectorCaster $this */
                if ($event->customerEmail()->value === $email) {
                    $state['found'] = true;

                    $this->stop();
                }

                return $state;
            })
            ->withQueryFilter($this->queryFilter($email))
            ->run(false);

        $query->getState()['found']
            ? $this->info("Customer $email found")
            : $this->warn("Customer $email not found");

        return self::SUCCESS;
    }

    private function queryFilter(string $email): QueryFilter
    {
        return new class($email) implements QueryFilter
        {
            public function __construct(private readonly string $email)
            {
            }

            public function apply(): callable
            {
                return function (Builder $query): void {
                    $query->whereJsonContains('content->customer_email', $this->email);
                };
            }
        };
    }
}
