<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use App\Controller\Api\Route\BuildController;
use App\Entity\Resource\MapObjectTypes;
use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\UpdatedAtTrait;
use App\Repository\MapObjectRepository;
use ArrayObject;
use DateTime;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ApiResource(
    description: 'реализует <a href="https://goo.su/abPDN">item info</a> и <a href="https://goo.su/kZZRY5">search item info</a> из дизайна'
)]
#[Get(
    normalizationContext: ['groups' => ['mapObject:read']],
    security: "is_granted('ROLE_USER')"
)]
#[GetCollection(
    paginationEnabled: false,
    normalizationContext: ['groups' => ['mapObject:read']],
    security: "is_granted('ROLE_USER')"
)]
#[Post(
    uriTemplate: '/route',
    controller: BuildController::class,
    openapi: new Operation(
        requestBody: new RequestBody(
            content: new ArrayObject([
                'application/json' => [
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'startId' => [
                                'type' => 'integer',
                                'example' => 1,
                                'required' => true
                            ],
                            'endId' => [
                                'type' => 'integer',
                                'example' => 2,
                                'required' => true
                            ]
                        ]
                    ]
                ]
            ])
        )
    ),
    security: "is_granted('ROLE_USER')"
)]
#[ORM\HasLifecycleCallbacks]
#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: MapObjectRepository::class)]
class MapObject
{
    use CreatedAtTrait;
    use UpdatedAtTrait;
    public const array TYPES = [
        MapObjectTypes::HOTEL->value => 'Гостиницы и отели',
        MapObjectTypes::RESTAURANT->value => 'Рестораны и места общения',
        MapObjectTypes::SIGHT->value => 'Достопримечательности',
        MapObjectTypes::PROJECT->value => 'Проекты',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('mapObject:read')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups('mapObject:read')]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups('mapObject:read')]
    private ?string $title = null;

    #[ORM\Column(length: 4096)]
    #[Groups('mapObject:read')]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Groups('mapObject:read')]
    private ?string $phone = null;

    #[ORM\Column(length: 255)]
    #[Groups('mapObject:read')]
    private ?string $address = null;

    #[ORM\Column(length: 255)]
    #[Groups('mapObject:read')]
    private ?string $email = null;

    #[ORM\Column(length: 1024)]
    #[Groups('mapObject:read')]
    private ?string $openingHours = null;

    #[ORM\Column]
    #[Groups('mapObject:read')]
    private ?string $objectType = null;

    #[ORM\Column(length: 512)]
    private ?string $mapUrl = null;

    #[ORM\Column(nullable: true)]
    private ?string $longitude = null;

    #[ORM\Column(nullable: true)]
    private ?string $latitude = null;

    #[Vich\UploadableField(mapping: 'map_object_images', fileNameProperty: 'image')]
    #[Assert\Image(mimeTypes: ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'])]
    private ?File $imageFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups('mapObject:read')]
    private ?string $image = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getOpeningHours(): ?string
    {
        return $this->openingHours;
    }

    public function setOpeningHours(string $openingHours): static
    {
        $this->openingHours = $openingHours;

        return $this;
    }

    public function getObjectType(): ?string
    {
        return $this->objectType;
    }

    public function setObjectType(string $objectType): static
    {
        $this->objectType = $objectType;

        return $this;
    }

    public function getMapUrl(): ?string
    {
        return $this->mapUrl;
    }

    public function setMapUrl(?string $mapUrl): static
    {
        $this->mapUrl = $mapUrl;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): static
    {
        $this->latitude = $latitude;

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
