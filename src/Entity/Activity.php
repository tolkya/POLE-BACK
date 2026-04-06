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
            read: false,
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            normalizationContext: ['groups' => ['activity:read']],
            denormalizationContext: ['groups' => ['activity:write']],
        ),
        new Patch(
            uriTemplate: '/activities/{id}',
            security: "is_granted('ACTIVITY_EDIT', object)",
            normalizationContext: ['groups' => ['activity:read']],
            denormalizationContext: ['groups' => ['activity:write']],
        ),
        new Delete(
            uriTemplate: '/activities/{id}',
            security: "is_granted('ACTIVITY_DELETE', object)",
        ),
    ],
)]
#[ORM\Entity(repositoryClass: ActivityRepository::class)]
class Activity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['activity:read', 'user_activity:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'activities')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['activity:read'])]
    private ?Club $club = null;

    #[ORM\ManyToOne(inversedBy: 'activities')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['activity:read', 'activity:write', 'user_activity:read'])]
    private ?ActivityType $activityType = null;

    /**
     * @var Collection<int, UserActivity>
     */
    #[ORM\OneToMany(targetEntity: UserActivity::class, mappedBy: 'activity', orphanRemoval: true)]
    private Collection $userActivities;

    #[ORM\Column]
    #[Groups(['activity:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['activity:read', 'activity:write', 'user_activity:read'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['activity:read', 'activity:write'])]
    private ?string $description = null;

    #[ORM\Column(enumType: ActivityStatus::class)]
    #[Groups(['activity:read'])]
    private ActivityStatus $status = ActivityStatus::ACTIVE;

    /**
     * @var Collection<int, Level>
     */
    #[ORM\OneToMany(targetEntity: Level::class, mappedBy: 'activity', orphanRemoval: true)]
    #[Groups(['activity:read'])]
    private Collection $levels;

    /**
     * @var Collection<int, ActivityMedia>
     */
    #[ORM\OneToMany(targetEntity: ActivityMedia::class, mappedBy: 'activity', orphanRemoval: true)]
    #[Groups(['activity:read'])]
    private Collection $medias;

    public function __construct()
    {
        $this->userActivities = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->levels = new ArrayCollection();
        $this->medias = new ArrayCollection();
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

    public function getStatus(): ActivityStatus
    {
        return $this->status;
    }

    public function setStatus(ActivityStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, Level>
     */
    public function getLevels(): Collection
    {
        return $this->levels;
    }

    public function addLevel(Level $level): static
    {
        if (!$this->levels->contains($level)) {
            $this->levels->add($level);
            $level->setActivity($this);
        }

        return $this;
    }

    public function removeLevel(Level $level): static
    {
        if ($this->levels->removeElement($level)) {
            // set the owning side to null (unless already changed)
            if ($level->getActivity() === $this) {
                $level->setActivity(null);
            }
        }

        return $this;
    }

    /** @return Collection<int, ActivityMedia> */
    public function getMedias(): Collection { return $this->medias; }

    public function addMedia(ActivityMedia $media): static
    {
        if (!$this->medias->contains($media)) {
            $this->medias->add($media);
            $media->setActivity($this);
        }
        return $this;
    }

    public function removeMedia(ActivityMedia $media): static
    {
        if ($this->medias->removeElement($media)) {
            if ($media->getActivity() === $this) {
                $media->setActivity(null);
            }
        }
        return $this;
    }
}
