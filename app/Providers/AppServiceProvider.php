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
use Chronhub\Storm\Producer\ProducerStrategy;
use Chronhub\Storm\Contracts\Routing\Registrar;
use BankRoute\Model\Customer\CustomerCollection;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Events\StatementPrepared;
use Chronhub\Storm\Contracts\Chronicler\Chronicler;
use App\Report\CustomerRegistration\AuthUserCreated;
use App\Report\CustomerRegistration\RegisterCustomer;
use Chronhub\Larastorm\Projection\ProjectionProvider;
use Chronhub\Storm\Reporter\Subscribers\ConsumeEvent;
use BankRoute\Model\Customer\Event\CustomerRegistered;
use Chronhub\Storm\Contracts\Reporter\ReporterManager;
use Chronhub\Storm\Publisher\EventPublisherSubscriber;
use Chronhub\Storm\Reporter\Subscribers\ConsumeCommand;
use BankRoute\Model\Customer\Service\UniqueCustomerEmail;
use BankRoute\ProcessManager\CustomerRegistrationProcess;
use Chronhub\Storm\Contracts\Chronicler\StreamSubscriber;
use Chronhub\Larastorm\Providers\LaraStormServiceProvider;
use Chronhub\Storm\Contracts\Chronicler\ChroniclerManager;
use App\Report\CustomerRegistration\RegisterCustomerStarted;
use BankRoute\Model\Customer\Handler\RegisterCustomerHandler;
use Chronhub\Storm\Support\Bridge\MakeCausationDomainCommand;
use App\Report\CustomerRegistration\CompleteCustomerRegistration;
use BankRoute\Infrastructure\Service\UniqueCustomerEmailFromRead;
use App\Report\CustomerRegistration\CustomerRegistrationCompleted;
use Chronhub\Storm\Contracts\Aggregate\AggregateRepositoryManager;
use Chronhub\Storm\Support\Bridge\HandleTransactionalDomainCommand;
use BankRoute\Infrastructure\Repository\CustomerEventStoreRepository;
use Chronhub\Storm\Contracts\Chronicler\TransactionalEventableChronicler;
use Chronhub\Storm\Contracts\Projector\ProjectionProvider as ProvideProjection;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(LaraStormServiceProvider::class);
        $this->app->register(BankServiceProvider::class);
        $this->app->register(SupervisorProjectorServiceProvider::class);

        $this->registerDefaultReporters();
        $this->registerCommandRoutes();
        $this->registerEventRoutes();
        $this->registerEventPublisher();
        $this->registerDefaultWriteChronicler();
        $this->registerAggregateRepositories();
        $this->registerAggregateServices();

        $this->app->singleton('projector.projection_provider.pgsql', function (Application $app): ProvideProjection {
            return new ProjectionProvider($app['db']->connection('pgsql'));
        });
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

    private function registerCommandRoutes(): void
    {
        $this->app->resolving(Registrar::class, function (Registrar $registrar): void {
            $group = $registrar->makeCommand('default');
            $group
                ->withHandlerMethod('command')
                ->withStrategy(ProducerStrategy::PER_MESSAGE->value)
                ->withQueue(['connection' => 'redis', 'name' => 'default'])
                ->withSubscribers(
                    ConsumeCommand::class,
                    HandleTransactionalDomainCommand::class,
                    MakeCausationDomainCommand::class,
                );

            $group->routes->addRoute(RegisterCustomer::class)->to(RegisterCustomerHandler::class);
        });

        $this->app->singleton(MakeCausationDomainCommand::class);
    }

    private function registerEventRoutes(): void
    {
        $this->app->resolving(Registrar::class, function (Registrar $registrar): void {
            $group = $registrar->makeEvent('default');
            $group
                ->withStrategy(ProducerStrategy::ASYNC->value)
                ->withQueue(['connection' => 'redis', 'name' => 'default'])
                ->withHandlerMethod('onEvent')
                ->withSubscribers(ConsumeEvent::class);

            //pm customer registration
            $group->routes->addRoute(RegisterCustomerStarted::class)->to(CustomerRegistrationProcess::class);

            $group->routes->addRoute(CustomerRegistered::class)->to(CustomerRegistrationProcess::class);

            $group->routes->addRoute(AuthUserCreated::class)
                ->to(CustomerRegistrationProcess::class);

            $group->routes->addRoute(CompleteCustomerRegistration::class)
                ->to(CustomerRegistrationCompleted::class);
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
