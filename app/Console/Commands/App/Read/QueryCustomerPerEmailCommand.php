<?php

declare(strict_types=1);

namespace App\Console\Commands\App\Read;

use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Chronhub\Larastorm\Support\Facade\Project;
use Chronhub\Storm\Contracts\Chronicler\QueryFilter;
use BankRoute\Model\Customer\Event\CustomerRegistered;
use Chronhub\Storm\Contracts\Projector\QueryCasterInterface;
use function filter_var;

final class QueryCustomerPerEmailCommand extends Command
{
    protected $signature = 'order:query-customer-per-email { email : customer email }';

    public function handle(): int
    {
        $email = $this->argument('email');

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $this->error("Invalid email $email");

            return self::FAILURE;
        }

        $query = Project::create('emit')->query();

        $query
            ->initialize(fn (): array => ['found' => false])
            ->fromStreams('customer')
            ->whenAny(function (CustomerRegistered $event, array $state) use ($email): array {
                /** @var QueryCasterInterface $this */
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
