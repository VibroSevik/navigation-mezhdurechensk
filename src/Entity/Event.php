<?php

namespace App\Entity;

use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\UpdatedAtTrait;
use App\Repository\EventRepository;
use DateTime;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\HasLifecycleCallbacks]
#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    use CreatedAtTrait;
    use UpdatedAtTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $date = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[Vich\UploadableField(mapping: 'event_images', fileNameProperty: 'image')]
    #[Assert\Image(mimeTypes: ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'])]
    private ?File $imageFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(string $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $imageFile): self
    {
        $this->imageFile = $imageFile;
        if (null !== $imageFile) {
            $this->updatedAt = new DateTime();
        }

        return $this;
    }
}
