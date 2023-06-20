<?php

declare(strict_types=1);

namespace App\Subscription;

use Temporal\WorkerFactory;
use Spiral\RoadRunner\Environment;
use Temporal\Worker\WorkerFactoryInterface;

class GreetWorkerFactory
{
    public function __construct()
    {

    }

    public function serve(?string $queue): void
    {
        $env = Environment::fromGlobals();

        logger()->info('TemporalServiceProvider::boot', [
            'mode' => $env->getMode(),
            'queue' => $queue,
        ]);

        if ($env->getMode() === Environment\Mode::MODE_TEMPORAL) {
            $factory = WorkerFactory::create();

            // Worker that listens on a task queue and hosts both workflow and activity implementations.
            $worker = $factory->newWorker(
                $queue ?? WorkerFactoryInterface::DEFAULT_TASK_QUEUE,
                \Temporal\Worker\WorkerOptions::new()->withMaxConcurrentActivityExecutionSize(1)
            );

            // Workflows are stateful. So you need a type to create instances.
            $worker->registerWorkflowTypes(GreetingWorkflow::class);

            // Activities are stateless and thread safe. So a shared instance is used.
            //$worker->registerActivity(\App\Subscription\GreetingActivity::class, fn (ReflectionClass $class) => $container->make($class->getName()));
            $worker->registerActivityImplementations(new GreetingActivity());

            // start primary loop
            $factory->run();
        }
    }
}
