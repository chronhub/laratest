<?php

declare(strict_types=1);

namespace App\Serializer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;

/**
 * @method  getSupportedTypes(?string $format)
 */
class HeaderNormalizer implements NormalizerInterface, DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function denormalize(mixed $data, string $type, string $format = null, array $context = [])
    {
        // TODO: Implement denormalize() method.
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null)
    {
        // TODO: Implement supportsDenormalization() method.
    }

    public function normalize(mixed $object, string $format = null, array $context = [])
    {
        // TODO: Implement normalize() method.
    }

    public function supportsNormalization(mixed $data, string $format = null)
    {

    }
}
