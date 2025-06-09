<?php

namespace App\Serializer;

use App\Entity\Event;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Vich\UploaderBundle\Storage\StorageInterface;

readonly class EventNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private NormalizerInterface $normalizer,

        private StorageInterface    $storage
    ) {
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        /* @var Event $object */
        $data = $this->normalizer->normalize($object, $format, $context);

        $data['image'] = $this->storage->resolveUri($object, 'imageFile');

        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Event;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Event::class => true,
        ];
    }
}