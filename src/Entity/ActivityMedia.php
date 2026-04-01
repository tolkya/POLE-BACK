<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Post;
use App\Repository\ActivityMediaRepository;
use App\State\ActivityMediaProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Attribute\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/activities/{activityId}/medias',
            uriVariables: ['activityId'],
            processor: ActivityMediaProcessor::class,
            read: false,
            deserialize: false,
            inputFormats: ['multipart' => ['multipart/form-data']],
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            normalizationContext: ['groups' => ['activity_media:read']],
        ),
        new Delete(
            uriTemplate: '/activity-medias/{id}',
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
        ),
    ],
)]
#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: ActivityMediaRepository::class)]
class ActivityMedia
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['activity_media:read', 'activity:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'medias')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Activity $activity = null;

    #[Vich\UploadableField(mapping: 'activity_media', fileNameProperty: 'filePath', mimeType: 'mimetype', originalName: 'originalName')]
    private ?File $file = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $filePath = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['activity_media:read', 'activity:read'])]
    private ?string $mimetype = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['activity_media:read', 'activity:read'])]
    private ?string $originalName = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['activity_media:read'])]
    private ?User $createdBy = null;

    #[ORM\Column]
    #[Groups(['activity_media:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    private ?string $mediaUrl = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[Groups(['activity_media:read', 'activity:read'])]
    public function getMediaUrl(): ?string { return $this->mediaUrl; }
    public function setMediaUrl(?string $mediaUrl): static { $this->mediaUrl = $mediaUrl; return $this; }

    public function getId(): ?int { return $this->id; }
    public function getActivity(): ?Activity { return $this->activity; }
    public function setActivity(?Activity $activity): static { $this->activity = $activity; return $this; }
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