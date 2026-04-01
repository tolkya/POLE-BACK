<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Post;
use App\Repository\ActivityTypeMediaRepository;
use App\State\ActivityTypeMediaProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Attribute\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/activity-types/{activityTypeId}/medias',
            uriVariables: ['activityTypeId'],
            processor: ActivityTypeMediaProcessor::class,
            read: false,
            deserialize: false,
            inputFormats: ['multipart' => ['multipart/form-data']],
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            normalizationContext: ['groups' => ['activity_type_media:read']],
        ),
        new Delete(
            uriTemplate: '/activity-type-medias/{id}',
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
        ),
    ],
)]
#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: ActivityTypeMediaRepository::class)]
class ActivityTypeMedia
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['activity_type_media:read', 'activity_type:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'medias')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ActivityType $activityType = null;

    #[Vich\UploadableField(mapping: 'activity_type_media', fileNameProperty: 'filePath', mimeType: 'mimetype', originalName: 'originalName')]
    private ?File $file = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $filePath = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['activity_type_media:read', 'activity_type:read'])]
    private ?string $mimetype = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['activity_type_media:read', 'activity_type:read'])]
    private ?string $originalName = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['activity_type_media:read'])]
    private ?User $createdBy = null;

    #[ORM\Column]
    #[Groups(['activity_type_media:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    private ?string $mediaUrl = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[Groups(['activity_type_media:read', 'activity_type:read'])]
    public function getMediaUrl(): ?string
    {
        return $this->mediaUrl;
    }

    public function setMediaUrl(?string $mediaUrl): static
    {
        $this->mediaUrl = $mediaUrl;
        return $this;
    }

    public function getId(): ?int { return $this->id; }

    public function getActivityType(): ?ActivityType { return $this->activityType; }
    public function setActivityType(?ActivityType $activityType): static { $this->activityType = $activityType; return $this; }

    public function getFile(): ?File { return $this->file; }
    public function setFile(?File $file): void { $this->file = $file; }

    public function getFilePath(): ?string { return $this->filePath; }
    public function setFilePath(?string $filePath): static { $this->filePath = $filePath; return $this; }

    public function getMimetype(): ?string { return $this->mimetype; }
    public function setMimetype(?string $mimetype): static { $this->mimetype = $mimetype; return $this; }

    public function getOriginalName(): ?string { return $this->originalName; }
    public function setOriginalName(?string $originalName): static { $this->originalName = $originalName; return $this; }

    public function getCreatedBy(): ?User { return $this->createdBy; }
    public function setCreatedBy(?User $createdBy): static { $this->createdBy = $createdBy; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }
}