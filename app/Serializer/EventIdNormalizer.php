<?php

declare(strict_types=1);

namespace App\Serializer;

use InvalidArgumentException;
use Symfony\Component\Uid\Uuid;
use Chronhub\Storm\Contracts\Message\Header;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use function gettype;
use function is_string;

/**
 * @method  getSupportedTypes(?string $format)
 */
final readonly class EventIdNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function normalize(mixed $object, string $format = null, array $context = []): string
    {
        $eventId = $this->extractEventId($object);

        return $eventId instanceof Uuid ? $eventId->jsonSerialize() : $eventId;
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): string|Uuid
    {
        $eventId = $this->extractEventId($data);

        return $eventId instanceof Uuid ? $eventId : Uuid::fromString($eventId);
    }

    public function supportsNormalization(mixed $data, string $format = null): bool
    {
        return $data instanceof Uuid || is_string($data);
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null): bool
    {
        return $type === Header::EVENT_ID;
    }

    private function extractEventId(mixed $data): string|Uuid
    {
        if ($data instanceof Uuid || is_string($data)) {
            return $data;
        }

        throw new InvalidArgumentException('Invalid Event id with given type: '.gettype($eventId));
    }
}
