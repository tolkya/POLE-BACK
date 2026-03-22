<?php

namespace App\Entity;

use App\Repository\ActivityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\ActivityStatus;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Delete;
use App\State\ActivitiesProvider;
use App\State\ActivityProcessor;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/clubs/{clubId}/activities',
            uriVariables: ['clubId'],
            provider: ActivitiesProvider::class,
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            normalizationContext: ['groups' => ['activity:read']],
        ),
        new Post(
            uriTemplate: '/clubs/{clubId}/activities',
            uriVariables: ['clubId'],
            processor: ActivityProcessor::class,
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            normalizationContext: ['groups' => ['activity:read']],
            denormalizationContext: ['groups' => ['activity:write']],
        ),
        new Patch(
            uriTemplate: '/activities/{id}',
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            normalizationContext: ['groups' => ['activity:read']],
            denormalizationContext: ['groups' => ['activity:write']],
        ),
        new Delete(
            uriTemplate: '/activities/{id}',
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
        ),
    ],
)]
#[ORM\Entity(repositoryClass: ActivityRepository::class)]
class Activity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['activity:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'activities')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['activity:read'])]
    private ?Club $club = null;

    #[ORM\ManyToOne(inversedBy: 'activities')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['activity:read', 'activity:write'])]
    private ?ActivityType $activityType = null;

    /**
     * @var Collection<int, UserActivity>
     */
    #[ORM\OneToMany(targetEntity: UserActivity::class, mappedBy: 'activity')]
    private Collection $userActivities;

    #[ORM\Column]
    #[Groups(['activity:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['activity:read', 'activity:write'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['activity:read', 'activity:write'])]
    private ?string $description = null;

    #[ORM\Column(enumType: ActivityStatus::class)]
    #[Groups(['activity:read'])]
    private ActivityStatus $status = ActivityStatus::ACTIVE;

    public function __construct()
    {
        $this->userActivities = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClub(): ?Club
    {
        return $this->club;
    }

    public function setClub(?Club $club): static
    {
        $this->club = $club;

        return $this;
    }

    public function getActivityType(): ?ActivityType
    {
        return $this->activityType;
    }

    public function setActivityType(?ActivityType $activityType): static
    {
        $this->activityType = $activityType;

        return $this;
    }

    /**
     * @return Collection<int, UserActivity>
     */
    public function getUserActivities(): Collection
    {
        return $this->userActivities;
    }

    public function addUserActivity(UserActivity $userActivity): static
    {
        if (!$this->userActivities->contains($userActivity)) {
            $this->userActivities->add($userActivity);
            $userActivity->setActivity($this);
        }

        return $this;
    }

    public function removeUserActivity(UserActivity $userActivity): static
    {
        if ($this->userActivities->removeElement($userActivity)) {
            // set the owning side to null (unless already changed)
            if ($userActivity->getActivity() === $this) {
                $userActivity->setActivity(null);
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }
}
