<?php

declare(strict_types=1);

namespace App\Subscription;

use Generator;
use DateInterval;
use Temporal\Workflow;
use Temporal\DataConverter\Type;
use Temporal\Workflow\WorkflowMethod;
use Temporal\Activity\ActivityOptions;

#[Workflow\WorkflowInterface]
class GreetingWorkflow
{
    private $greetingActivity;

    public function __construct()
    {
        $this->greetingActivity = Workflow::newActivityStub(
            GreetingActivity::class,
            ActivityOptions::new()->withScheduleToCloseTimeout(DateInterval::createFromDateString('10 seconds'))
        );
    }

    #[WorkflowMethod]
    #[Workflow\ReturnType(Type::TYPE_STRING)]
    public function greet(string $name): Generator
    {
        // This is a blocking call that returns only after the activity has completed.
        return yield $this->greetingActivity->composeGreeting('Hello', $name);
    }
}
