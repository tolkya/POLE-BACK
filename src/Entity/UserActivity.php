<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Enum\ActivityRole;
use App\Enum\UserActivityStatus;
use App\Repository\UserActivityRepository;
use App\State\ActivityJoinProcessor;
use App\State\ActivityMembersProvider;
use App\State\UserActivityStatusProcessor;
use App\State\UserActivityProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        // Admin inscrit directement un membre → APPROVED
        new Post(
            uriTemplate: '/activities/{activityId}/members',
            uriVariables: ['activityId'],
            read: false,
            normalizationContext: ['groups' => ['user_activity:read']],
            denormalizationContext: ['groups' => ['user_activity:write']],
            security: 'is_granted("IS_AUTHENTICATED_FULLY")',
            processor: UserActivityProcessor::class,
        ),
        // Membre s'auto-inscrit → PENDING
        new Post(
            uriTemplate: '/activities/{activityId}/join',
            uriVariables: ['activityId'],
            read: false,
            normalizationContext: ['groups' => ['user_activity:read']],
            denormalizationContext: ['groups' => ['user_activity:write']],
            security: 'is_granted("IS_AUTHENTICATED_FULLY")',
            processor: ActivityJoinProcessor::class,
        ),
        // Liste des inscrits d'une activité
        new GetCollection(
            uriTemplate: '/activities/{activityId}/members',
            uriVariables: ['activityId'],
            normalizationContext: ['groups' => ['user_activity:read']],
            security: 'is_granted("IS_AUTHENTICATED_FULLY")',
            provider: ActivityMembersProvider::class,
        ),
        // Changer le status (APPROVED/REJECTED)
        new Patch(
            uriTemplate: '/user-activities/{id}',
            normalizationContext: ['groups' => ['user_activity:read']],
            denormalizationContext: ['groups' => ['user_activity:write']],
            security: 'is_granted("ACTIVITY_MEMBER_MANAGE", object)',
            processor: UserActivityStatusProcessor::class,
        ),
        // Désinscrire
        new Delete(
            uriTemplate: '/user-activities/{id}',
            security: 'is_granted("ACTIVITY_MEMBER_MANAGE", object) or is_granted("ACTIVITY_SELF_LEAVE", object)',
        ),
    ]
)]
#[ORM\Entity(repositoryClass: UserActivityRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_USER_ACTIVITY', columns: ['member_id', 'activity_id'])]
class UserActivity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user_activity:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userActivities')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['user_activity:read'])]
    private ?User $member = null;

    #[ORM\ManyToOne(inversedBy: 'userActivities')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['user_activity:read'])]
    private ?Activity $activity = null;

    #[ORM\Column(length: 20, enumType: ActivityRole::class)]
    #[Groups(['user_activity:read', 'user_activity:write'])]
    private ?ActivityRole $role = null;

    #[ORM\Column(length: 20, enumType: UserActivityStatus::class)]
    #[Groups(['user_activity:read', 'user_activity:write'])]
    private ?UserActivityStatus $status = null;

    #[ORM\Column]
    #[Groups(['user_activity:read'])]
    private ?\DateTimeImmutable $createdAt = null;
    
    #[Groups(['user_activity:write'])]
    private ?int $memberId = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMember(): ?User
    {
        return $this->member;
    }

    public function setMember(?User $member): static
    {
        $this->member = $member;
        return $this;
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

    public function getRole(): ?ActivityRole
    {
        return $this->role;
    }

    public function setRole(ActivityRole $role): static
    {
        $this->role = $role;
        return $this;
    }

    public function getStatus(): ?UserActivityStatus
    {
        return $this->status;
    }

    public function setStatus(UserActivityStatus $status): static
    {
        $this->status = $status;
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

    public function getMemberId(): ?int
    {
        return $this->memberId;
    }

    public function setMemberId(?int $memberId): static
    {
        $this->memberId = $memberId;
        return $this;
    }
}