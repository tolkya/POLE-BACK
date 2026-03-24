<?php

namespace App\Entity;

use App\Enum\LevelValue;
use App\Repository\LevelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\State\LevelProcessor;
use App\State\LevelsProvider;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/activities/{activityId}/levels',
            uriVariables: ['activityId'],
            provider: LevelsProvider::class,
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            normalizationContext: ['groups' => ['level:read']],
        ),
        new Post(
            uriTemplate: '/activities/{activityId}/levels',
            uriVariables: ['activityId'],
            processor: LevelProcessor::class,
            read: false,
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            normalizationContext: ['groups' => ['level:read']],
            denormalizationContext: ['groups' => ['level:write']],
        ),
        new Patch(
            uriTemplate: '/levels/{id}',
            security: "is_granted('LEVEL_EDIT', object)",
            normalizationContext: ['groups' => ['level:read']],
            denormalizationContext: ['groups' => ['level:write']],
        ),
        new Delete(
            uriTemplate: '/levels/{id}',
            security: "is_granted('LEVEL_DELETE', object)",
        ),
    ],
)]
#[ORM\Entity(repositoryClass: LevelRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_LEVEL_ACTIVITY_VALUE', columns: ['activity_id', 'value'])]
class Level
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['level:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'levels')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Activity $activity = null;

    #[ORM\Column(length: 20, enumType: LevelValue::class)]
    #[Assert\NotNull]
    #[Groups(['level:read', 'level:write'])]
    private ?LevelValue $value = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['level:read', 'level:write'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['level:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, Skill>
     */
    #[ORM\OneToMany(targetEntity: Skill::class, mappedBy: 'level', orphanRemoval: true)]
    private Collection $skills;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->skills = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getActivity(): ?Activity
    {
        return $this->activity;
    }

    public function setActivity(?Activity $activity): static
    {
        $this->activity = $activity;

        return $this;
    }

    public function getValue(): ?LevelValue
    {
        return $this->value;
    }

    public function setValue(LevelValue $value): static
    {
        $this->value = $value;

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

    /**
     * @return Collection<int, Skill>
     */
    public function getSkills(): Collection
    {
        return $this->skills;
    }

    public function addSkill(Skill $skill): static
    {
        if (!$this->skills->contains($skill)) {
            $this->skills->add($skill);
            $skill->setLevel($this);
        }

        return $this;
    }

    public function removeSkill(Skill $skill): static
    {
        if ($this->skills->removeElement($skill)) {
            // set the owning side to null (unless already changed)
            if ($skill->getLevel() === $this) {
                $skill->setLevel(null);
            }
        }

        return $this;
    }
}