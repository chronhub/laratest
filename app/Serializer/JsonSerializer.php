<?php

declare(strict_types=1);

namespace App\Serializer;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\UidNormalizer;

class JsonSerializer
{
    private Serializer $serializer;

    public function __construct()
    {
        $this->serializer = new Serializer(
            [new UidNormalizer()],
            [new JsonEncoder()]
        );
    }

    public function serialize(array $data, array $context = []): string
    {
        return $this->serializer->serialize($data, 'json', $context);
    }

    public function deserialize(string $data, array $context = []): array
    {
        return $this->serializer->deserialize($data, 'json', 'json', $context);
    }
}
