<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\MainWindowRepository;
use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\UpdatedAtTrait;
use DateTime;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(
    description: 'медиа для главного экрана',
)]
#[Get(
    normalizationContext: ['groups' => ['main_window:read']],
    security: "is_granted('ROLE_USER')"
)]
#[GetCollection(
    paginationEnabled: false,
    normalizationContext: ['groups' => ['main_window:read']],
    security: "is_granted('ROLE_USER')",
)]
#[ORM\HasLifecycleCallbacks]
#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: MainWindowRepository::class)]
class MainWindow
{
    use createdAtTrait;
    use updatedAtTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Vich\UploadableField(mapping: 'main_window_media', fileNameProperty: 'media')]
    #[Assert\File(extensions: ['mp4', 'webm'])]
    private ?File $mediaFile = null;

    #[ORM\Column(length: 255)]
    private ?string $media = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMedia(): ?string
    {
        return $this->media;
    }

    public function setMedia(string $media): static
    {
        $this->media = $media;

        return $this;
    }

    public function getMediaFile(): ?File
    {
        return $this->mediaFile;
    }

    public function setMediaFile(?File $mediaFile): self
    {
        $this->mediaFile = $mediaFile;
        if (null !== $mediaFile) {
            $this->updatedAt = new DateTime();
        }

        return $this;
    }
}
