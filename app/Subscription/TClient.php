<?php

declare(strict_types=1);

namespace App\Subscription;

use Temporal\Client\WorkflowClient;
use Temporal\Client\WorkflowOptions;
use Temporal\Client\GRPC\ServiceClient;

class TClient
{
    public readonly WorkflowClient $client;

    public function __construct()
    {
        $this->client = WorkflowClient::create(ServiceClient::create('172.17.0.1:7233'));
    }

    public function newWorkflowStub(string $class, WorkflowOptions $options = null)
    {
        return $this->client->newWorkflowStub($class, $options);
    }
}
