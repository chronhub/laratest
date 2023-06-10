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
use Chronhub\Storm\Reporter\DomainType;
use Illuminate\Support\ServiceProvider;
use BankRoute\Model\Product\GetProducts;
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
use App\Report\Order\MarkOrderAsProcessingPayment;
use BankRoute\Model\Order\Handler\PayOrderHandler;
use App\Testing\StopPropagationOnTimeoutSubscriber;
use BankRoute\Model\Customer\Query\GetCustomerById;
use BankRoute\ProcessManager\RenewOrderOnOrderPaid;
use BankRoute\Model\Order\Handler\StartOrderHandler;
use BankRoute\Model\Order\Query\GetFullPendingOrder;
use BankRoute\Model\Order\Query\GetOrderByIdHandler;
use App\Report\Customer\Signup\CustomerSignupStarted;
use BankRoute\Model\Order\Handler\CancelOrderHandler;
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
use BankRoute\Model\Order\Query\GetModifiedOrdersForPayment;
use BankRoute\Model\Customer\Handler\RegisterCustomerHandler;
use Chronhub\Storm\Support\Bridge\MakeCausationDomainCommand;
use BankRoute\Model\Order\Event\OrderMarkedAsProcessingPayment;
use BankRoute\ProcessManager\CreateOrderOnCustomerRegistration;
use BankRoute\Model\Order\Query\GetFullPreparedForPaymentOrders;
use BankRoute\Model\Order\Handler\DecreaseOrderItemQuantityHandler;
use BankRoute\Model\Order\Query\GetModifiedOrdersForPaymentHandler;
use Chronhub\Storm\Support\Bridge\HandleTransactionalDomainCommand;
use App\Report\Customer\Signup\SendActivationEmailOnSignUpCompleted;
use BankRoute\Model\Order\Handler\MarkOrderAsProcessingPaymentHandler;
use BankRoute\Model\Order\Query\GetFullPreparedForPaymentOrdersHandler;

class MessageRouteServiceProvider extends ServiceProvider
{
    protected array $routing = [
        [
            'command' => [
                [
                    'name' => 'default',
                    'routes' => [
                        [RegisterCustomer::class, RegisterCustomerHandler::class],
                        [StartOrder::class, StartOrderHandler::class, ['name' => 'order']],
                        [CancelOrder::class, CancelOrderHandler::class, ['name' => 'payment']],
                        [AddOrderItem::class, AddOrderItemHandler::class, ['name' => 'order']],
                        [RemoveOrderItem::class, RemoveOrderItemHandler::class, ['name' => 'order']],
                        [DecreaseOrderItemQuantity::class, DecreaseOrderItemQuantityHandler::class, ['name' => 'order', 'delay' => 5, 'backoff' => 5]],
                        [MarkOrderAsProcessingPayment::class, MarkOrderAsProcessingPaymentHandler::class, ['name' => 'payment', 'backoff' => 5]],
                        [PayOrder::class, PayOrderHandler::class, ['name' => 'payment']],
                    ],
                    'queue' => ['connection' => 'rabbitmq', 'name' => 'customer', 'timeout' => 10],
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
                        [OrderCreated::class, null, ['name' => 'order']],
                        [OrderModified::class, null, ['name' => 'order']],
                        [OrderCanceled::class, RenewOrderOnOrderCanceled::class, ['name' => 'order']],
                        [OrderItemAdded::class, null, ['name' => 'order']],
                        [OrderItemRemoved::class, null, ['name' => 'order']],
                        [OrderItemQuantityIncreased::class, null, ['name' => 'order']],
                        [OrderItemQuantityDecreased::class, null, ['name' => 'order']],
                        [OrderMarkedAsProcessingPayment::class, null, ['name' => 'payment']],
                        [OrderPaid::class, RenewOrderOnOrderPaid::class, ['name' => 'order']],
                    ],
                    'queue' => ['connection' => 'rabbitmq', 'name' => 'customer', 'timeout' => 10],
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
                        [GetFullPreparedForPaymentOrders::class, GetFullPreparedForPaymentOrdersHandler::class],
                        [GetModifiedOrdersForPayment::class, GetModifiedOrdersForPaymentHandler::class],
                        [GetProducts::class, GetProductsHandler::class],
                    ],
                ],
            ],
        ],
    ];

    public function register(): void
    {
        $this->app->resolving(Registrar::class, function (Registrar $router): void {
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
        if ($group->getType() === DomainType::COMMAND) {
            $group
                ->withStrategy(ProducerStrategy::ASYNC->value)
                ->withHandlerMethod('command')
                ->withSubscribers(
                    ConsumeCommand::class,
                    HandleTransactionalDomainCommand::class,
                    MakeCausationDomainCommand::class,
                    // StopPropagationOnTimeoutSubscriber::class,
                );
        }

        if ($group->getType() === DomainType::EVENT) {
            $group
                ->withStrategy(ProducerStrategy::PER_MESSAGE->value)
                ->withHandlerMethod('onEvent')
                ->withSubscribers(ConsumeEvent::class);
        }

        if ($group->getType() === DomainType::QUERY) {
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
