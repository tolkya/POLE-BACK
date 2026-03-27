<?php

namespace App\Entity;

use App\Repository\SkillRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\State\SkillProcessor;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\State\SkillsProvider;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/levels/{levelId}/skills',
            uriVariables: ['levelId'],
            provider: SkillsProvider::class,
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            normalizationContext: ['groups' => ['skill:read']],
        ),
        new Post(
            uriTemplate: '/levels/{levelId}/skills',
            uriVariables: ['levelId'],
            processor: SkillProcessor::class,
            read: false,
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            normalizationContext: ['groups' => ['skill:read']],
            denormalizationContext: ['groups' => ['skill:write']],
        ),
        new Patch(
            uriTemplate: '/skills/{id}',
            security: "is_granted('SKILL_EDIT', object)",
            normalizationContext: ['groups' => ['skill:read']],
            denormalizationContext: ['groups' => ['skill:write']],
        ),
        new Delete(
            uriTemplate: '/skills/{id}',
            security: "is_granted('SKILL_DELETE', object)",
        ),
    ],
)]
#[ORM\Entity(repositoryClass: SkillRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_SKILL_LEVEL_NAME', columns: ['level_id', 'name'])]
class Skill
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['skill:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'skills')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Level $level = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['skill:read', 'skill:write'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['skill:read', 'skill:write'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['skill:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['skill:read'])]
    private ?User $createdBy = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLevel(): ?Level
    {
        return $this->level;
    }

    public function setLevel(?Level $level): static
    {
        $this->level = $level;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

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
}
