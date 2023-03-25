<?php

declare(strict_types=1);

namespace App\Providers;

use PDO;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Chronhub\Storm\Reporter\ReportEvent;
use Chronhub\Storm\Reporter\ReportQuery;
use Chronhub\Storm\Publisher\PublishEvent;
use Chronhub\Storm\Reporter\ReportCommand;
use BankRoute\Model\Order\Service\OrderList;
use BankRoute\Model\Customer\CustomerCollection;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Events\StatementPrepared;
use Chronhub\Storm\Contracts\Chronicler\Chronicler;
use Chronhub\Larastorm\Projection\ProjectionProvider;
use Chronhub\Storm\Contracts\Reporter\ReporterManager;
use Chronhub\Storm\Publisher\EventPublisherSubscriber;
use BankRoute\Model\Customer\Service\UniqueCustomerEmail;
use Chronhub\Storm\Contracts\Chronicler\StreamSubscriber;
use Chronhub\Larastorm\Providers\LaraStormServiceProvider;
use Chronhub\Storm\Contracts\Chronicler\ChroniclerManager;
use Chronhub\Storm\Support\Bridge\MakeCausationDomainCommand;
use BankRoute\Infrastructure\Service\UniqueCustomerEmailFromRead;
use BankRoute\Infrastructure\Repository\OrderEventStoreRepository;
use Chronhub\Storm\Contracts\Aggregate\AggregateRepositoryManager;
use BankRoute\Infrastructure\Repository\CustomerEventStoreRepository;
use Chronhub\Storm\Contracts\Chronicler\TransactionalEventableChronicler;
use Chronhub\Storm\Contracts\Projector\ProjectionProvider as ProvideProjection;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(LaraStormServiceProvider::class);
        $this->app->register(MessageRouteServiceProvider::class);
        $this->app->register(SupervisorProjectorServiceProvider::class);

        $this->registerDefaultReporters();
        $this->registerEventPublisher();
        $this->registerDefaultWriteChronicler();
        $this->registerAggregateRepositories();
        $this->registerAggregateServices();

        $this->app->singleton('projector.projection_provider.pgsql', function (Application $app): ProvideProjection {
            return new ProjectionProvider($app['db']->connection('pgsql'));
        });

        $this->app->singleton(MakeCausationDomainCommand::class);
    }

    public function boot(): void
    {
        Event::listen(StatementPrepared::class, function ($event) {
            $event->statement->setFetchMode(PDO::FETCH_ASSOC);
        });
    }

    private function registerDefaultReporters(): void
    {
        $this->app->singleton(
            ReportCommand::class,
            fn (Application $app): ReportCommand => $app[ReporterManager::class]->command()
        );

        $this->app->singleton(
            ReportEvent::class,
            fn (Application $app): ReportEvent => $app[ReporterManager::class]->event()
        );

        $this->app->singleton(
            ReportQuery::class,
            fn (Application $app): ReportQuery => $app[ReporterManager::class]->query()
        );
    }

    private function registerEventPublisher(): void
    {
        $this->app->bind(EventPublisherSubscriber::class, function (Application $app): StreamSubscriber {
            return new EventPublisherSubscriber(
                new PublishEvent($app[ReportEvent::class])
            );
        });
    }

    private function registerDefaultWriteChronicler(): void
    {
        $this->app->bind(Chronicler::class, function (Application $app): TransactionalEventableChronicler {
            return $app[ChroniclerManager::class]
                ->setDefaultDriver('connection')
                ->create('write');
        });
    }

    private function registerAggregateRepositories(): void
    {
        $this->app->singleton(
            CustomerCollection::class,
            fn (Application $app): CustomerCollection => new CustomerEventStoreRepository(
                $app[AggregateRepositoryManager::class]->create('customer')
            )
        );

        $this->app->singleton(
            OrderList::class,
            fn (Application $app): OrderList => new OrderEventStoreRepository(
                $app[AggregateRepositoryManager::class]->create('order')
            )
        );
    }

    private function registerAggregateServices(): void
    {
        $this->app->bind(UniqueCustomerEmail::class, UniqueCustomerEmailFromRead::class);
    }
}
