<?php

namespace App\Entity;

use App\Repository\NotificationReceiptRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use App\State\NotificationReceiptProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(
            security: "is_granted('ROLE_SUPER_ADMIN')",
            provider: NotificationReceiptProvider::class,
        ),
        new Patch(
            security: "is_granted('ROLE_SUPER_ADMIN') and object.getRecipient() == user",
        ),
    ],
    normalizationContext: ['groups' => ['receipt:read']],
    denormalizationContext: ['groups' => ['receipt:update']],
)]

#[ORM\Entity(repositoryClass: NotificationReceiptRepository::class)]
class NotificationReceipt
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['receipt:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'notificationReceipts')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['receipt:read'])]
    private ?NotificationEvent $event = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $recipient = null;

    #[ORM\Column]
    #[Groups(['receipt:read', 'receipt:update'])]
    private bool $isRead = false;

    #[ORM\Column(nullable: true)]
    #[Groups(['receipt:read'])]
    private ?\DateTimeImmutable $readAt = null;

    #[ORM\Column]
    #[Groups(['receipt:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->isRead = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): ?NotificationEvent
    {
        return $this->event;
    }

    public function setEvent(?NotificationEvent $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function getRecipient(): ?User
    {
        return $this->recipient;
    }

    public function setRecipient(?User $recipient): static
    {
        $this->recipient = $recipient;

        return $this;
    }

    public function GetIsRead(): bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): static
    {
        $this->isRead = $isRead;
        if ($isRead && $this->readAt === null) {
            $this->readAt = new \DateTimeImmutable();
        }

        return $this;
    }
    public function getReadAt(): ?\DateTimeImmutable
    {
        return $this->readAt;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

}
