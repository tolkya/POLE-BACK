<?php

namespace App\Entity;

use App\Repository\ClubRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\State\CreateClubProcessor;
use Symfony\Component\Serializer\Attribute\Groups;
use App\Enum\JoinPolicy;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

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
        new Post(
            uriTemplate: '/clubs',
            security: "is_granted('ROLE_USER')",
            processor: CreateClubProcessor::class,
            denormalizationContext: ['groups' => ['club:write']],
        ),
    ],
    normalizationContext: ['groups' => ['club:read']],
)]

#[Vich\Uploadable]
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

    #[ORM\Column(length: 20)]
    #[Groups(['club:read', 'club:write'])]
    #[Assert\Choice(callback: [JoinPolicy::class, 'values'], message: 'La politique d\'adhésion "{{ value }}" est invalide.')]
    private string $joinPolicy = JoinPolicy::AUTO_ACCEPT->value;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['club:read', 'club:write'])]
    private ?string $description = null;

    #[ORM\Column(length: 7, nullable: true)]
    #[Groups(['club:read', 'club:write'])]
    private ?string $themeColor = null;

    #[Vich\UploadableField(mapping: 'club_logo', fileNameProperty: 'logoFilename')]
    private ?File $logoFile = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $logoFilename = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['club:read', 'club:write'])]
    private ?string $street = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['club:read', 'club:write'])]
    private ?string $postalCode = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['club:read', 'club:write'])]
    private ?string $city = null;

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

    #[Groups(['club:read', 'user_club:read'])]
    public function getLogoUrl(): ?string
    {
        return $this->logoFilename ? '/media/clubs/logos/' . $this->logoFilename : null;
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

    public function getJoinPolicy(): ?string
    {
        return $this->joinPolicy;
    }

    public function setJoinPolicy(string $joinPolicy): static
    {
        $this->joinPolicy = $joinPolicy;

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

    public function getThemeColor(): ?string
    {
        return $this->themeColor;
    }

    public function setThemeColor(?string $themeColor): static
    {
        $this->themeColor = $themeColor;

        return $this;
    }

    public function getLogoFile(): ?File
    {
        return $this->logoFile;
    }

    public function setLogoFile(?File $file = null): void
    {
        $this->logoFile = $file;
        if ($file !== null) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getLogoFilename(): ?string
    {
        return $this->logoFilename;
    }

    public function setLogoFilename(?string $logoFilename): static
    {
        $this->logoFilename = $logoFilename;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): static
    {
        $this->street = $street;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this;
    }
}
