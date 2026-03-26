<?php

namespace App\Entity;

use App\Enum\ActivityRole;
use App\Enum\UserActivityStatus;
use App\Repository\UserActivityRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

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
}