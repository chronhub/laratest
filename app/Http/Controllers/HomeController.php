<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Str;
use App\Subscription\TClient;
use App\Subscription\GreetingWorkflow;

final class HomeController
{
    public function __invoke(TClient $client)
    {
        $greet = $client->newWorkflowStub(
            GreetingWorkflow::class,
            //WorkflowOptions::new()->withWorkflowId('greeting-'.Str::random())
        );

        // async
        $run = $client->client->start($greet, fake()->name);

        // sync
        //return $greet->greet(fake()->name);

        //$run->getResult(null, 2);

        return 'ok';
    }
}
