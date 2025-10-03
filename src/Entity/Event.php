<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\UpdatedAtTrait;
use App\Repository\EventRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ApiResource(
    description: 'реализует <a href="https://goo.su/zXV63Um">news</a> и <a href="https://goo.su/XpatP">news inner</a> из дизайна'
)]
#[Get(
    normalizationContext: ['groups' => ['event:read']],
    security: "is_granted('ROLE_USER')"
)]
#[GetCollection(
    paginationEnabled: false,
    normalizationContext: ['groups' => ['event:read']],
    security: "is_granted('ROLE_USER')"
)]
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
    #[Groups(['event:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['event:read'])]
    private ?string $date = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['event:read'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['event:read'])]
    private ?string $shortDescription = null;

    #[Vich\UploadableField(mapping: 'event_images', fileNameProperty: 'image')]
    #[Assert\Image(mimeTypes: ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'])]
    private ?File $imageFile = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['event:read'])]
    private ?string $image = null;

    /**
     * @var Collection<int, EventData>
     */
    #[ORM\OneToMany(targetEntity: EventData::class, mappedBy: 'event', cascade: ['all'])]
    #[Groups(['event:read'])]
    private Collection $eventData;

    public function eventDataToString(): string
    {
        $sum = '';
        foreach ($this->eventData as $eventDataItem) {
            $sum .= $eventDataItem->getTitle() . PHP_EOL . $eventDataItem->getDescription() . PHP_EOL . PHP_EOL;
        }
        return $sum;
    }

    public function __construct()
    {
        $this->eventData = new ArrayCollection();
    }

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

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(string $shortDescription): static
    {
        $this->shortDescription = $shortDescription;

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

    /**
     * @return Collection<int, EventData>
     */
    public function getEventData(): Collection
    {
        return $this->eventData;
    }

    public function addEventData(EventData $eventData): static
    {
        if (!$this->eventData->contains($eventData)) {
            $this->eventData->add($eventData);
            $eventData->setEvent($this);
        }

        return $this;
    }

    public function removeEventData(EventData $eventData): static
    {
        if ($this->eventData->removeElement($eventData)) {
            // set the owning side to null (unless already changed)
            if ($eventData->getEvent() === $this) {
                $eventData->setEvent(null);
            }
        }

        return $this;
    }
}
