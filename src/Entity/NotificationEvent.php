<?php

namespace App\Entity;

use App\Repository\NotificationEventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: NotificationEventRepository::class)]
class NotificationEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['receipt:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['receipt:read'])]
    private ?string $notifType = null;

    #[ORM\Column(length: 30, nullable: true)]
    #[Groups(['receipt:read'])]
    private ?string $subjectType = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['receipt:read'])]
    private ?int $subjectId = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['receipt:read'])]
    private ?array $context = null;

    #[ORM\ManyToOne]
    #[Groups(['receipt:read'])]
    private ?User $triggeredBy = null;

    #[ORM\Column]
    #[Groups(['receipt:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, NotificationReceipt>
     */
    #[ORM\OneToMany(targetEntity: NotificationReceipt::class, mappedBy: 'event', orphanRemoval: true)]
    private Collection $notificationReceipts;

    public function __construct()
    {
        $this->notificationReceipts = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNotifType(): ?string
    {
        return $this->notifType;
    }

    public function setNotifType(string $notifType): static
    {
        $this->notifType = $notifType;

        return $this;
    }

    public function getSubjectType(): ?string
    {
        return $this->subjectType;
    }

    public function setSubjectType(?string $subjectType): static
    {
        $this->subjectType = $subjectType;

        return $this;
    }

    public function getSubjectId(): ?int
    {
        return $this->subjectId;
    }

    public function setSubjectId(?int $subjectId): static
    {
        $this->subjectId = $subjectId;

        return $this;
    }

    public function getContext(): ?array
    {
        return $this->context;
    }

    public function setContext(?array $context): static
    {
        $this->context = $context;

        return $this;
    }

    public function getTriggeredBy(): ?User
    {
        return $this->triggeredBy;
    }

    public function setTriggeredBy(?User $triggeredBy): static
    {
        $this->triggeredBy = $triggeredBy;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }


    /**
     * @return Collection<int, NotificationReceipt>
     */
    public function getNotificationReceipts(): Collection
    {
        return $this->notificationReceipts;
    }

    public function addNotificationReceipt(NotificationReceipt $notificationReceipt): static
    {
        if (!$this->notificationReceipts->contains($notificationReceipt)) {
            $this->notificationReceipts->add($notificationReceipt);
            $notificationReceipt->setEvent($this);
        }

        return $this;
    }

    public function removeNotificationReceipt(NotificationReceipt $notificationReceipt): static
    {
        if ($this->notificationReceipts->removeElement($notificationReceipt)) {
            // set the owning side to null (unless already changed)
            if ($notificationReceipt->getEvent() === $this) {
                $notificationReceipt->setEvent(null);
            }
        }

        return $this;
    }
}
