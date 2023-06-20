<?php

declare(strict_types=1);

namespace App\Providers;

use PDO;
use App\Api\ApiClient;
use App\Api\ApiChronicler;
use App\Api\ApiStreamEventLoader;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Chronhub\Storm\Reporter\ReportEvent;
use Chronhub\Storm\Reporter\ReportQuery;
use Chronhub\Storm\Publisher\PublishEvent;
use Chronhub\Storm\Reporter\ReportCommand;
use BankRoute\Model\Order\Service\OrderList;
use App\Subscription\TemporalServiceProvider;
use BankRoute\Model\Customer\CustomerCollection;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Events\StatementPrepared;
use Chronhub\Storm\Contracts\Chronicler\Chronicler;
use BankRoute\Projection\Customer\CustomerReadModel;
use Chronhub\Larastorm\Projection\ConnectionProvider;
use Chronhub\Storm\Contracts\Reporter\ReporterManager;
use Chronhub\Storm\Publisher\EventPublisherSubscriber;
use BankRoute\Model\Customer\Service\UniqueCustomerEmail;
use Chronhub\Larastorm\Providers\SnapshotServiceProvider;
use Chronhub\Storm\Contracts\Chronicler\StreamSubscriber;
use Chronhub\Larastorm\Providers\LaraStormServiceProvider;
use Chronhub\Storm\Contracts\Chronicler\ChroniclerManager;
use Chronhub\Storm\Contracts\Projector\ProjectionProvider;
use Chronhub\Storm\Support\Bridge\MakeCausationDomainCommand;
use BankRoute\Infrastructure\Service\UniqueCustomerEmailFromRead;
use BankRoute\Infrastructure\Repository\OrderEventStoreRepository;
use Chronhub\Larastorm\Support\Contracts\AggregateRepositoryManager;
use BankRoute\Infrastructure\Repository\CustomerEventStoreRepository;
use Chronhub\Storm\Contracts\Chronicler\TransactionalEventableChronicler;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(LaraStormServiceProvider::class);
        $this->app->register(MessageRouteServiceProvider::class);
        $this->app->register(SupervisorProjectorServiceProvider::class);
        $this->app->register(SnapshotServiceProvider::class);

        $this->registerDefaultReporters();
        $this->registerEventPublisher();
        $this->registerDefaultChronicler();
        $this->registerAggregateRepositories();
        $this->registerAggregateServices();

        $this->app->singleton(
            'projector.projection_provider.pgsql',
            fn (Application $app): ProjectionProvider => new ConnectionProvider($app['db']->connection('pgsql'))
        );

        $this->app->singleton(
            'projector.projection_provider.mysql',
            fn (Application $app): ProjectionProvider => new ConnectionProvider($app['db']->connection('mysql'))
        );

        $this->app->singleton(MakeCausationDomainCommand::class);

        //        $this->app->when(CustomerReadModel::class)
        //            ->needs('$connection')
        //            ->give('mysql');

        $this->app->register(TemporalServiceProvider::class);
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

        $this->app->alias(ReportCommand::class, 'reporter.command.default');

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
        $this->app->singleton(EventPublisherSubscriber::class, function (Application $app): StreamSubscriber {
            return new EventPublisherSubscriber(
                new PublishEvent($app[ReportEvent::class])
            );
        });
    }

    private function registerDefaultChronicler(): void
    {
        $this->app->bind(Chronicler::class, function (Application $app): TransactionalEventableChronicler {
            return $app[ChroniclerManager::class]
                ->setDefaultDriver('connection')
                ->create('write');
        });

        $this->app->bind('chronicler.api.read', function (Application $app): Chronicler {
            $chronicler = $app[ChroniclerManager::class]
                ->setDefaultDriver('connection')
                ->create('read');

            return new ApiChronicler(new ApiClient(), $chronicler, $app[ApiStreamEventLoader::class]);
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
