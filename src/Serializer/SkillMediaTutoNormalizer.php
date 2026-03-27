<?php

namespace App\Serializer;

use App\Entity\SkillMediaTuto;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Vich\UploaderBundle\Storage\StorageInterface;

final class SkillMediaTutoNormalizer implements NormalizerInterface
{
    public function __construct(
        private readonly NormalizerInterface $normalizer,
        private readonly StorageInterface $storage,
    ) {}

    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        /** @var SkillMediaTuto $object */
        $object->setMediaUrl($this->storage->resolveUri($object, 'file'));

        return $this->normalizer->normalize($object, $format, $context);
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof SkillMediaTuto;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [SkillMediaTuto::class => true];
    }
}