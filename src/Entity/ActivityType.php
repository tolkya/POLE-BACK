<?php

namespace App\Entity;

use App\Repository\ActivityTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\State\ActivityTypeProcessor;
use App\Enum\ActivityTypeStatus;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;

#[ApiFilter(SearchFilter::class, properties: ['name' => 'ipartial'])]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/activity-types',
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
        ),
        new Get(
            uriTemplate: '/activity-types/{id}',
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
        ),
        new Post(
            uriTemplate: '/activity-types',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            processor: ActivityTypeProcessor::class,
        ),
        new Patch(
            uriTemplate: '/activity-types/{id}',
            security: "is_granted('ROLE_SUPER_ADMIN')",
        ),
        new Delete(
            uriTemplate: '/activity-types/{id}',
            security: "is_granted('ROLE_SUPER_ADMIN')",
        ),
    ],
    normalizationContext: ['groups' => ['activity_type:read']],
    denormalizationContext: ['groups' => ['activity_type:write']],
)]

#[ORM\Entity(repositoryClass: ActivityTypeRepository::class)]
class ActivityType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['activity_type:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['activity_type:read', 'activity_type:write'])]
    private ?string $name = null;

    #[ORM\ManyToOne]
    private ?User $createdBy = null;

    /**
     * @var Collection<int, Activity>
     */
    #[ORM\OneToMany(targetEntity: Activity::class, mappedBy: 'activityType')]
    private Collection $activities;

    #[ORM\Column]
    #[Groups(['activity_type:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['activity_type:read', 'activity_type:write'])]
    private ?string $description = null;

    #[ORM\Column(enumType: ActivityTypeStatus::class)]
    #[Groups(['activity_type:read'])]
    private ActivityTypeStatus $status = ActivityTypeStatus::ACTIVE;

    public function __construct()
    {
        $this->activities = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

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

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * @return Collection<int, Activity>
     */
    public function getActivities(): Collection
    {
        return $this->activities;
    }

    public function addActivity(Activity $activity): static
    {
        if (!$this->activities->contains($activity)) {
            $this->activities->add($activity);
            $activity->setActivityType($this);
        }

        return $this;
    }

    public function removeActivity(Activity $activity): static
    {
        if ($this->activities->removeElement($activity)) {
            // set the owning side to null (unless already changed)
            if ($activity->getActivityType() === $this) {
                $activity->setActivityType(null);
            }
        }

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): ActivityTypeStatus
    {
        return $this->status;
    }

    public function setStatus(ActivityTypeStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

}
