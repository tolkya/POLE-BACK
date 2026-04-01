<?php

namespace App\Serializer;

use App\Entity\ActivityMedia;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Vich\UploaderBundle\Storage\StorageInterface;

final class ActivityMediaNormalizer implements NormalizerInterface
{
    public function __construct(
        private readonly NormalizerInterface $normalizer,
        private readonly StorageInterface $storage,
    ) {}

    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        /** @var ActivityMedia $object */
        $object->setMediaUrl($this->storage->resolveUri($object, 'file'));

        return $this->normalizer->normalize($object, $format, $context);
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof ActivityMedia;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [ActivityMedia::class => true];
    }
}