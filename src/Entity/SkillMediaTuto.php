<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Post;
use App\Repository\SkillMediaTutoRepository;
use App\State\SkillMediaTutoProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Attribute\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/skills/{skillId}/tutos',
            uriVariables: ['skillId'],
            processor: SkillMediaTutoProcessor::class,
            read: false,
            deserialize: false,
            inputFormats: ['multipart' => ['multipart/form-data']],
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            normalizationContext: ['groups' => ['skill_media_tuto:read']],
        ),
        new Delete(
            uriTemplate: '/skill-media-tutos/{id}',
            security: "is_granted('SKILL_MEDIA_TUTO_DELETE', object)",
        ),
    ],
)]
#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: SkillMediaTutoRepository::class)]
class SkillMediaTuto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['skill_media_tuto:read', 'skill:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'skillMediaTutos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Skill $skill = null;

    #[Vich\UploadableField(mapping: 'skill_media_tuto', fileNameProperty: 'filePath', mimeType: 'mimetype', originalName: 'originalName')]
    #[Groups(['skill_media_tuto:write'])]
    private ?File $file = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $filePath = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['skill_media_tuto:read', 'skill:read'])]
    private ?string $mimetype = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['skill_media_tuto:read', 'skill:read'])]
    private ?string $originalName = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['skill_media_tuto:read', 'skill:read'])]
    private ?User $createdBy = null;

    #[ORM\Column]
    #[Groups(['skill_media_tuto:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    private ?string $mediaUrl = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[Groups(['skill_media_tuto:read', 'skill:read'])]
    public function getMediaUrl(): ?string
    {
        return $this->mediaUrl;
    }

    public function setMediaUrl(?string $mediaUrl): static
    {
        $this->mediaUrl = $mediaUrl;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSkill(): ?Skill
    {
        return $this->skill;
    }

    public function setSkill(?Skill $skill): static
    {
        $this->skill = $skill;

        return $this;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): static
    {
        $this->file = $file;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): static
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function getMimetype(): ?string
    {
        return $this->mimetype;
    }

    public function setMimetype(?string $mimetype): static
    {
        $this->mimetype = $mimetype;

        return $this;
    }

    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    public function setOriginalName(?string $originalName): static
    {
        $this->originalName = $originalName;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
