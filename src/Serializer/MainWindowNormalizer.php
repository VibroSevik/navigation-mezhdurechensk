<?php

namespace App\Serializer;

use App\Entity\MainWindow;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Vich\UploaderBundle\Storage\StorageInterface;

readonly class MainWindowNormalizer implements NormalizerInterface
{
    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private NormalizerInterface $normalizer,

        private StorageInterface    $storage
    ) {
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        /* @var MainWindow $object */
        $data = $this->normalizer->normalize($object, $format, $context);

        $data['source'] = $this->storage->resolveUri($object, 'mediaFile');
        $data['type'] = str_contains($object->getMedia(), '.mp4') || str_contains($object->getMedia(), '.webm') ? 'video' : 'image';

        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof MainWindow;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            MainWindow::class => true,
        ];
    }
}