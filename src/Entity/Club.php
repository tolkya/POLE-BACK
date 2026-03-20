<?php

namespace App\Entity;

use App\Repository\ClubRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/clubs',
            security: "is_granted('ROLE_SUPER_ADMIN')",
        ),
        new Get(
            uriTemplate: '/clubs/{id}',
            security: "is_granted('ROLE_SUPER_ADMIN') or is_granted('CLUB_ADMIN', object)",
        ),
        new Patch(
            uriTemplate: '/clubs/{id}',
            security: "is_granted('ROLE_SUPER_ADMIN') or is_granted('CLUB_ADMIN', object)",
            denormalizationContext: ['groups' => ['club:write']],
        ),
    ],
    normalizationContext: ['groups' => ['club:read']],
)]

#[ORM\Entity(repositoryClass: ClubRepository::class)]
class Club
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['club:read', 'user_club:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['club:read', 'user_club:read', 'club:write'])]
    private ?string $name = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['club:read', 'club:write'])]
    private ?string $phone = null;

    #[ORM\Column(length: 180, nullable: true)]
    #[Groups(['club:read', 'club:write'])]
    private ?string $email = null;

    #[ORM\Column]
    #[Groups(['club:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, Activity>
     */
    #[ORM\OneToMany(targetEntity: Activity::class, mappedBy: 'club', orphanRemoval: true)]
    private Collection $activities;

    /**
     * @var Collection<int, UserClub>
     */
    #[ORM\OneToMany(targetEntity: UserClub::class, mappedBy: 'club')]
    private Collection $userClubs;

    public function __construct()
    {
        $this->activities = new ArrayCollection();
        $this->userClubs = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    #[Groups(['club:read'])]
    public function getClubCode(): ?string
    {
        return $this->id !== null ? 'cde_' . $this->id : null;
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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

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
            $activity->setClub($this);
        }

        return $this;
    }

    public function removeActivity(Activity $activity): static
    {
        if ($this->activities->removeElement($activity)) {
            // set the owning side to null (unless already changed)
            if ($activity->getClub() === $this) {
                $activity->setClub(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserClub>
     */
    public function getUserClubs(): Collection
    {
        return $this->userClubs;
    }

    public function addUserClub(UserClub $userClub): static
    {
        if (!$this->userClubs->contains($userClub)) {
            $this->userClubs->add($userClub);
            $userClub->setClub($this);
        }

        return $this;
    }

    public function removeUserClub(UserClub $userClub): static
    {
        if ($this->userClubs->removeElement($userClub)) {
            // set the owning side to null (unless already changed)
            if ($userClub->getClub() === $this) {
                $userClub->setClub(null);
            }
        }

        return $this;
    }
}
