<?php

declare(strict_types=1);

namespace App\Api;

use stdClass;
use Generator;
use Chronhub\Storm\Stream\StreamName;
use Chronhub\Storm\Chronicler\Exceptions\StreamNotFound;
use Chronhub\Storm\Contracts\Chronicler\StreamEventLoader;
use Chronhub\Storm\Chronicler\Exceptions\NoStreamEventReturn;
use Chronhub\Storm\Contracts\Serializer\StreamEventSerializer;
use function count;
use function is_iterable;
use function array_key_exists;
use function iterator_to_array;

final readonly class ApiStreamEventLoader implements StreamEventLoader
{
    public function __construct(private StreamEventSerializer $serializer)
    {
    }

    public function query(mixed $streamEvents, StreamName $streamName): Generator
    {
        if (is_iterable($streamEvents)) {
            $streamEvents = $this->generate($streamEvents, $streamName);

            yield from $streamEvents;

            return $streamEvents->getReturn();
        }

        throw StreamNotFound::withStreamName($streamName);
    }

    /**
     * @throws NoStreamEventReturn
     * @throws StreamNotFound
     */
    private function generate(iterable $streamEvents, StreamName $streamName): Generator
    {
        $streamEvents = $this->normalizeStreamEvents($streamEvents, $streamName);

        $count = count($streamEvents);

        if ($count === 0) {
            throw NoStreamEventReturn::withStreamName($streamName);
        }

        foreach ($streamEvents as $streamEvent) {
            if ($streamEvent instanceof stdClass) {
                $streamEvent = (array) $streamEvent;
            }

            yield $this->serializer->deserializePayload($streamEvent);
        }

        return $count;
    }

    private function normalizeStreamEvents(iterable $streamEvents, StreamName $streamName): array
    {
        $streamEvents = iterator_to_array($streamEvents);

        $tableName = '_'.$streamName->toString();

        if (array_key_exists($tableName, $streamEvents)) {
            $streamEvents = $streamEvents[$tableName];
        }

        return $streamEvents;
    }
}
