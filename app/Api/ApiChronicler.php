<?php

declare(strict_types=1);

namespace App\Api;

use Http;
use Generator;
use Chronhub\Storm\Stream\Stream;
use Chronhub\Storm\Stream\StreamName;
use Illuminate\Http\Client\PendingRequest;
use Chronhub\Storm\Contracts\Chronicler\Chronicler;
use Chronhub\Storm\Contracts\Chronicler\QueryFilter;
use Chronhub\Storm\Contracts\Aggregate\AggregateIdentity;
use Chronhub\Storm\Contracts\Chronicler\EventStreamProvider;

final readonly class ApiChronicler implements Chronicler
{
    private PendingRequest $client;

    private string $endPoint;

    public function __construct(
        protected Chronicler $chronicler,
        protected ApiStreamEventLoader $streamEventLoader
    ) {
        $this->client = Http::acceptJson()->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ]);

        $this->endPoint = 'http://172.17.0.1:8080/api/rest';
    }

    public function firstCommit(Stream $stream): void
    {
        $this->chronicler->firstCommit($stream);
    }

    public function amend(Stream $stream): void
    {
        $this->chronicler->amend($stream);
    }

    public function delete(StreamName $streamName): void
    {
        $this->chronicler->delete($streamName);
    }

    public function getEventStreamProvider(): EventStreamProvider
    {
        return $this->chronicler->getEventStreamProvider();
    }

    public function retrieveAll(StreamName $streamName, AggregateIdentity $aggregateId, string $direction = 'asc'): Generator
    {
        return $this->chronicler->retrieveAll($streamName, $aggregateId, $direction);
    }

    public function retrieveFiltered(StreamName $streamName, QueryFilter $queryFilter): Generator
    {
        $cb = $queryFilter->apply();

        $parameters = $cb();

        $parameters += ['endpoint' => $this->endPoint];

        $response = $this->client->withUrlParameters($parameters)->get($parameters['template']);

        return $this->streamEventLoader->query($response->json(), $streamName);
    }

    public function filterStreamNames(StreamName ...$streamNames): array
    {
        return $this->chronicler->filterStreamNames(...$streamNames);
    }

    public function filterCategoryNames(string ...$categoryNames): array
    {
        return $this->chronicler->filterCategoryNames(...$categoryNames);
    }

    public function hasStream(StreamName $streamName): bool
    {
        return $this->chronicler->hasStream($streamName);
    }
}
