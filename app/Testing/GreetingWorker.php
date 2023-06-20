<?php

declare(strict_types=1);

use Temporal\WorkerFactory;

\ini_set('display_errors', 'stderr');
include 'vendor/autoload.php';

$rrEnv = \Spiral\RoadRunner\Environment::fromGlobals();

// factory initiates and runs task queue specific activity and workflow workers
$factory = WorkerFactory::create();

// Worker that listens on a task queue and hosts both workflow and activity implementations.
$worker = $factory->newWorker(
    \Temporal\Worker\WorkerFactoryInterface::DEFAULT_TASK_QUEUE,
    \Temporal\Worker\WorkerOptions::new()->withMaxConcurrentActivityExecutionSize(1)
);

// Workflows are stateful. So you need a type to create instances.
$worker->registerWorkflowTypes(\App\Subscription\GreetingWorkflow::class);

// Activities are stateless and thread safe. So a shared instance is used.
//$worker->registerActivity(\App\Subscription\GreetingActivity::class, fn (ReflectionClass $class) => $container->make($class->getName()));
$worker->registerActivityImplementations(new \App\Subscription\GreetingActivity());

// start primary loop
$factory->run();
