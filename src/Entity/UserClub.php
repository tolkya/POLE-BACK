<?php

namespace App\Entity;

use App\Repository\UserClubRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\State\ClubMembersProvider;
use App\Enum\ClubRole;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/clubs/{clubId}/members',
            uriVariables: ['clubId'],
            provider: ClubMembersProvider::class,
            normalizationContext: ['groups' => ['club_member:read']],
        ),
    ],
)]
#[ORM\Entity(repositoryClass: UserClubRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_USER_CLUB', columns: ['member_id', 'club_id'])]
class UserClub
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userClubs')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['user_club:read', 'club_member:read'])]
    private ?User $member = null;

    #[ORM\ManyToOne(inversedBy: 'userClubs')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['user_club:read'])]
    private ?Club $club = null;

    #[ORM\Column]
    #[Groups(['club_member:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['user_club:read', 'club_member:read'])]
    #[Assert\All([
        new Assert\Choice(choices: ClubRole::values(), message: 'Le rôle "{{ value }}" est invalide.'),
    ])]
    private array $roles = [];

    #[ORM\Column(nullable: true)]
    #[Groups(['user_club:read', 'club_member:read'])]
    private ?\DateTimeImmutable $validatedAt = null;

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

    public function getClub(): ?Club
    {
        return $this->club;
    }

    public function setClub(?Club $club): static
    {
        $this->club = $club;

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

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getValidatedAt(): ?\DateTimeImmutable
    {
        return $this->validatedAt;
    }

    public function setValidatedAt(?\DateTimeImmutable $validatedAt): static
    {
        $this->validatedAt = $validatedAt;

        return $this;
    }
}
