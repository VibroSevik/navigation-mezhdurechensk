<?php

namespace App\Serializer;

use App\Entity\MapObject;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Vich\UploaderBundle\Storage\StorageInterface;

readonly class MapObjectNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private NormalizerInterface $normalizer,

        private StorageInterface    $storage
    ) {
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        /* @var MapObject $object */
        $data = $this->normalizer->normalize($object, $format, $context);

        $data['image'] = $this->storage->resolveUri($object, 'imageFile');
        $data['coordinates'] = [$object->getLatitude(), $object->getLongitude()];

        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof MapObject;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            MapObject::class => true,
        ];
    }
}