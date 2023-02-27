<?php

declare(strict_types=1);

namespace App\Providers;

use App\Report\RegisterCustomer;
use Illuminate\Support\ServiceProvider;
use App\Report\NotifyCustomerRegistered;
use Chronhub\Storm\Reporter\ReportEvent;
use Chronhub\Storm\Reporter\ReportQuery;
use Chronhub\Storm\Publisher\PublishEvent;
use Chronhub\Storm\Reporter\ReportCommand;
use Chronhub\Storm\Producer\ProducerStrategy;
use Chronhub\Storm\Contracts\Routing\Registrar;
use BankRoute\Model\Customer\CustomerCollection;
use Illuminate\Contracts\Foundation\Application;
use Chronhub\Storm\Contracts\Chronicler\Chronicler;
use Chronhub\Larastorm\Providers\CqrsServiceProvider;
use Chronhub\Storm\Reporter\Subscribers\ConsumeEvent;
use BankRoute\Model\Customer\Event\CustomerRegistered;
use Chronhub\Storm\Contracts\Reporter\ReporterManager;
use Chronhub\Storm\Publisher\EventPublisherSubscriber;
use Chronhub\Storm\Reporter\Subscribers\ConsumeCommand;
use BankRoute\Model\Customer\Service\UniqueCustomerEmail;
use Chronhub\Larastorm\Providers\MessagerServiceProvider;
use Chronhub\Storm\Contracts\Chronicler\StreamSubscriber;
use Chronhub\Larastorm\Providers\ProjectorServiceProvider;
use Chronhub\Storm\Contracts\Chronicler\ChroniclerManager;
use Chronhub\Larastorm\Providers\ChroniclerServiceProvider;
use Chronhub\Larastorm\Support\Bridge\MakeCausationCommand;
use BankRoute\Model\Customer\Handler\RegisterCustomerHandler;
use BankRoute\Infrastructure\Service\UniqueCustomerEmailFromRead;
use Chronhub\Larastorm\Support\Bridge\HandleTransactionalCommand;
use Chronhub\Storm\Contracts\Aggregate\AggregateRepositoryManager;
use BankRoute\Infrastructure\Repository\CustomerEventStoreRepository;
use Chronhub\Storm\Contracts\Chronicler\TransactionalEventableChronicler;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(MessagerServiceProvider::class);
        $this->app->register(CqrsServiceProvider::class);
        $this->app->register(ChroniclerServiceProvider::class);
        $this->app->register(ProjectorServiceProvider::class);
        $this->app->register(BankServiceProvider::class);
        $this->app->register(SupervisorProjectorServiceProvider::class);

        $this->registerDefaultReporters();
        $this->registerCommandRoutes();
        $this->registerEventRoutes();
        $this->registerEventPublisher();
        $this->registerDefaultWriteChronicler();
        $this->registerAggregateRepositories();
        $this->registerAggregateServices();
    }

    public function boot(): void
    {
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

    private function registerCommandRoutes(): void
    {
        $this->app->resolving(Registrar::class, function (Registrar $registrar): void {
            $group = $registrar->makeCommand('default');
            $group
                ->withMessageHandlerMethodName('command')
                ->withProducerStrategy(ProducerStrategy::ASYNC->value)
                ->withQueue(['connection' => 'redis', 'queue' => 'default'])
                ->withMessageSubscribers(
                    ConsumeCommand::class,
                    HandleTransactionalCommand::class,
                    MakeCausationCommand::class,
                );

            $group->routes->addRoute(RegisterCustomer::class)->to(RegisterCustomerHandler::class);
        });

        $this->app->singleton(MakeCausationCommand::class);
    }

    private function registerEventRoutes(): void
    {
        $this->app->resolving(Registrar::class, function (Registrar $registrar): void {
            $group = $registrar->makeEvent('default');
            $group
                ->withProducerStrategy(ProducerStrategy::SYNC->value)
                ->withMessageHandlerMethodName('onEvent')
                ->withMessageSubscribers(ConsumeEvent::class);

            $group->routes->addRoute(CustomerRegistered::class)->to(
                NotifyCustomerRegistered::class
            );
        });
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
    }

    private function registerAggregateServices(): void
    {
        $this->app->bind(UniqueCustomerEmail::class, UniqueCustomerEmailFromRead::class);
    }
}
