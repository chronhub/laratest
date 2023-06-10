<?php

declare(strict_types=1);

namespace App\Api;

use Generator;
use Chronhub\Storm\Stream\Stream;
use Chronhub\Storm\Stream\StreamName;
use Chronhub\Storm\Contracts\Chronicler\Chronicler;
use Chronhub\Storm\Contracts\Chronicler\QueryFilter;
use Chronhub\Storm\Contracts\Aggregate\AggregateIdentity;
use Chronhub\Storm\Contracts\Chronicler\EventStreamProvider;

final readonly class ApiChronicler implements Chronicler
{
    public function __construct(
        protected ApiClient $client,
        protected Chronicler $chronicler,
        protected ApiStreamEventLoader $streamEventLoader
    ) {
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

            $parameters += ['endpoint' => $this->client->endPoint];

            $response = $this->client->request->withUrlParameters($parameters)->get($parameters['template']);

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
