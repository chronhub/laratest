<?php

declare(strict_types=1);

namespace App\Providers;

use Arr;
use InvalidArgumentException;
use App\Report\Order\PayOrder;
use App\Report\Order\StartOrder;
use App\Report\Order\CancelOrder;
use Chronhub\Storm\Routing\Group;
use App\Report\Order\AddOrderItem;
use App\Report\Order\RemoveOrderItem;
use Chronhub\Storm\Routing\EventGroup;
use Chronhub\Storm\Routing\QueryGroup;
use Illuminate\Support\ServiceProvider;
use BankRoute\Model\Product\GetProducts;
use Chronhub\Storm\Routing\CommandGroup;
use BankRoute\Model\Order\Event\OrderPaid;
use BankRoute\Model\Order\Event\OrderCreated;
use BankRoute\Model\Order\Query\GetOrderById;
use Chronhub\Storm\Producer\ProducerStrategy;
use BankRoute\Model\Order\Event\OrderCanceled;
use BankRoute\Model\Order\Event\OrderModified;
use App\Report\Customer\Signup\AuthUserCreated;
use App\Report\Order\DecreaseOrderItemQuantity;
use BankRoute\Model\Order\Event\OrderItemAdded;
use BankRoute\Model\Product\GetProductsHandler;
use Chronhub\Storm\Contracts\Routing\Registrar;
use App\Report\Customer\Signup\RegisterCustomer;
use BankRoute\Model\Order\Event\OrderItemRemoved;
use BankRoute\Model\Order\Query\GetFullOrderById;
use BankRoute\Model\Order\Handler\PayOrderHandler;
use BankRoute\Model\Customer\Query\GetCustomerById;
use BankRoute\ProcessManager\RenewOrderOnOrderPaid;
use BankRoute\Model\Order\Handler\StartOrderHandler;
use BankRoute\Model\Order\Query\GetFullPendingOrder;
use BankRoute\Model\Order\Query\GetOrderByIdHandler;
use App\Report\Customer\Signup\CustomerSignupStarted;
use BankRoute\Model\Order\Handler\CancelOrderHandler;
use BankRoute\Model\Order\Query\GetFullPendingOrders;
use Chronhub\Storm\Reporter\Subscribers\ConsumeEvent;
use Chronhub\Storm\Reporter\Subscribers\ConsumeQuery;
use BankRoute\Model\Customer\Event\CustomerRegistered;
use BankRoute\Model\Order\Handler\AddOrderItemHandler;
use App\Report\Customer\Signup\CustomerSignupCompleted;
use BankRoute\ProcessManager\RenewOrderOnOrderCanceled;
use Chronhub\Storm\Reporter\Subscribers\ConsumeCommand;
use BankRoute\Model\Order\Query\GetFullOrderByIdHandler;
use BankRoute\Model\Order\Handler\RemoveOrderItemHandler;
use BankRoute\ProcessManager\CustomerRegistrationProcess;
use BankRoute\Model\Customer\Query\GetCustomerByIdHandler;
use BankRoute\Model\Order\Event\OrderItemQuantityDecreased;
use BankRoute\Model\Order\Event\OrderItemQuantityIncreased;
use BankRoute\Model\Order\Query\GetFullPendingOrderHandler;
use BankRoute\Model\Order\Query\GetFullPendingOrdersHandler;
use BankRoute\Model\Customer\Handler\RegisterCustomerHandler;
use Chronhub\Storm\Support\Bridge\MakeCausationDomainCommand;
use BankRoute\ProcessManager\CreateOrderOnCustomerRegistration;
use BankRoute\Model\Order\Handler\DecreaseOrderItemQuantityHandler;
use Chronhub\Storm\Support\Bridge\HandleTransactionalDomainCommand;
use App\Report\Customer\Signup\SendActivationEmailOnSignUpCompleted;

class MessageRouteServiceProvider extends ServiceProvider
{
    protected array $routing = [
        [
            'command' => [
                [
                    'name' => 'default',
                    'routes' => [
                        [RegisterCustomer::class, RegisterCustomerHandler::class],
                        [StartOrder::class, StartOrderHandler::class, ['name' => 'orders']],
                        [CancelOrder::class, CancelOrderHandler::class],
                        [AddOrderItem::class, AddOrderItemHandler::class, ['name' => 'orders']],
                        [RemoveOrderItem::class, RemoveOrderItemHandler::class, ['name' => 'orders']],
                        [DecreaseOrderItemQuantity::class, DecreaseOrderItemQuantityHandler::class, ['name' => 'orders', 'delay' => 5, 'backoff' => 5]],
                        [PayOrder::class, PayOrderHandler::class],
                    ],
                    'queue' => ['connection' => 'redis', 'name' => 'default', 'timeout' => 10],
                ],
            ],

            'event' => [
                [
                    'name' => 'default',
                    'routes' => [
                        [CustomerSignupStarted::class, CustomerRegistrationProcess::class],
                        [CustomerRegistered::class, CreateOrderOnCustomerRegistration::class],
                        [AuthUserCreated::class, CustomerRegistrationProcess::class],
                        [CustomerSignupCompleted::class, [
                            SendActivationEmailOnSignUpCompleted::class,
                            CreateOrderOnCustomerRegistration::class,
                        ]],
                        [OrderCreated::class],
                        [OrderModified::class],
                        [OrderCanceled::class, RenewOrderOnOrderCanceled::class],
                        [OrderItemAdded::class],
                        [OrderItemRemoved::class],
                        [OrderItemQuantityIncreased::class],
                        [OrderItemQuantityDecreased::class],
                        [OrderPaid::class, RenewOrderOnOrderPaid::class],
                    ],
                    'queue' => ['connection' => 'redis', 'name' => 'default', 'timeout' => 10],
                ],
            ],

            'query' => [
                [
                    'name' => 'default',
                    'routes' => [
                        [GetCustomerById::class, GetCustomerByIdHandler::class],
                        [GetOrderById::class, GetOrderByIdHandler::class],
                        [GetFullOrderById::class, GetFullOrderByIdHandler::class],
                        [GetFullPendingOrder::class, GetFullPendingOrderHandler::class],
                        [GetFullPendingOrders::class, GetFullPendingOrdersHandler::class],
                        [GetProducts::class, GetProductsHandler::class],
                    ],
                ],
            ],
        ],
    ];

    public function register(): void
    {
        $this->app->resolving(Registrar::class, function (Registrar $router) {
            $this->registerRoutes($router);
        });
    }

    protected function registerRoutes(Registrar $registrar): void
    {
        foreach ($this->routing as $routing) {
            foreach ($routing as $type => $config) {
                foreach ($config as $options) {
                    $group = $this->makeGroup($registrar, $type, $options['name'], $options['queue'] ?? []);

                    foreach ($options['routes'] as $route) {
                        $group->routes
                            ->addRoute($route[0])
                            ->to(...Arr::wrap($route[1] ?? null))
                            ->onQueue($route[2] ?? []);
                    }
                }
            }
        }
    }

    protected function configDefaultGroup(Group $group, array $queue = []): Group
    {
        if ($group instanceof CommandGroup) {
            $group
                ->withStrategy(ProducerStrategy::ASYNC->value)
                ->withHandlerMethod('command')
                ->withSubscribers(
                    ConsumeCommand::class,
                    HandleTransactionalDomainCommand::class,
                    MakeCausationDomainCommand::class,
                );
        }

        if ($group instanceof EventGroup) {
            $group
                ->withStrategy(ProducerStrategy::PER_MESSAGE->value)
                ->withHandlerMethod('onEvent')
                ->withSubscribers(ConsumeEvent::class);
        }

        if ($group instanceof QueryGroup) {
            $group
                ->withStrategy(ProducerStrategy::SYNC->value)
                ->withHandlerMethod('query')
                ->withSubscribers(ConsumeQuery::class);
        }

        if (! empty($queue)) {
            $group->withQueue($queue);
        }

        return $group;
    }

    protected function makeGroup(Registrar $registrar, string $type, string $name, array $queue = []): Group
    {
        $group = match ($type) {
            'command' => $registrar->makeCommand($name),
            'query' => $registrar->makeQuery($name),
            'event' => $registrar->makeEvent($name),
            default => throw new InvalidArgumentException("Invalid message type $type"),
        };

        return $this->configDefaultGroup($group, $queue);
    }
}
