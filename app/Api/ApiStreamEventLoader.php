<?php

declare(strict_types=1);

namespace App\Api;

use stdClass;
use Generator;
use Chronhub\Storm\Stream\StreamName;
use Illuminate\Database\QueryException;
use Chronhub\Storm\Chronicler\Exceptions\StreamNotFound;
use Chronhub\Storm\Contracts\Chronicler\StreamEventLoader;
use Chronhub\Storm\Chronicler\Exceptions\NoStreamEventReturn;
use Chronhub\Storm\Contracts\Serializer\StreamEventSerializer;
use function array_key_exists;
use function iterator_to_array;

readonly class ApiStreamEventLoader implements StreamEventLoader
{
    public function __construct(private StreamEventSerializer $serializer)
    {
    }

    /**
     * @throws StreamNotFound
     */
    private function call(iterable $streamEvents, StreamName $streamName): Generator
    {
        try {
            $count = 0;

            $streamEvents = iterator_to_array($streamEvents);

            $tableName = '_'.$streamName->toString();

            if (array_key_exists($tableName, $streamEvents)) {
                $streamEvents = $streamEvents[$tableName];
            }

            foreach ($streamEvents as $streamEvent) {
                if ($streamEvent instanceof stdClass) {
                    $streamEvent = (array) $streamEvent;
                }

                yield $this->serializer->deserializePayload($streamEvent);

                $count++;
            }

            if ($count === 0) {
                throw NoStreamEventReturn::withStreamName($streamName);
            }

            return $count;
        } catch (QueryException $exception) {
            if ($exception->getCode() !== '00000') {
                throw StreamNotFound::withStreamName($streamName);
            }
        }
    }

    public function query(array $streamEvents, StreamName $streamName): Generator
    {
        $streamEvents = $this->call($streamEvents, $streamName);

        yield from $streamEvents;

        return $streamEvents->getReturn();
    }
}
