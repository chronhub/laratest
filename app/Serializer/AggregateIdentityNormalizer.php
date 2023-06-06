<?php

declare(strict_types=1);

namespace App\Serializer;

use InvalidArgumentException;
use Chronhub\Storm\Contracts\Message\EventHeader;
use Chronhub\Storm\Contracts\Aggregate\AggregateIdentity;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use function is_array;
use function is_string;
use function class_exists;
use function is_subclass_of;

/**
 * @method  getSupportedTypes(?string $format)
 */
class AggregateIdentityNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        [$aggregateId, $aggregateIdType] = $this->validateHeader($object, $context);

        return [
            EventHeader::AGGREGATE_ID => is_string($aggregateId) ? $aggregateId : $aggregateId->toString(),
            EventHeader::AGGREGATE_ID_TYPE => $aggregateIdType,
        ];
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): array
    {
        [$aggregateId, $aggregateIdType] = $this->validateHeader($data, $context);

        return [
            EventHeader::AGGREGATE_ID => $aggregateIdType::fromString($aggregateId),
            EventHeader::AGGREGATE_ID_TYPE => $aggregateIdType,
        ];
    }

    public function supportsNormalization(mixed $data, string $format = null): bool
    {
        return $data instanceof AggregateIdentity;
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null): bool
    {
        return is_array($data) && isset($data[EventHeader::AGGREGATE_ID]);
    }

    /**
     * @return array{0: string|AggregateIdentity, 1: class-string<AggregateIdentity>}
     */
    private function validateHeader(mixed $data, array $context): array
    {
        if (! is_array($data)) {
            throw new InvalidArgumentException('Invalid data given, must be array');
        }

        $aggregateId = $data[EventHeader::AGGREGATE_ID] ?? null;

        if ($aggregateId === null) {
            throw new InvalidArgumentException('Aggregate id not found in header');
        }

        $aggregateIdType = $data[EventHeader::AGGREGATE_ID_TYPE] ?? null;

        if (! class_exists($aggregateIdType) || ! is_subclass_of($aggregateIdType, AggregateIdentity::class)) {
            throw new InvalidArgumentException('Aggregate id type not found in header');
        }

        return [$aggregateId, $aggregateIdType];
    }
}
