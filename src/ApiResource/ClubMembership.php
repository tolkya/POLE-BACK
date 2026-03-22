<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\State\ClubMembershipProcessor;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/user-clubs/join',
            processor: ClubMembershipProcessor::class,
            security: 'is_granted("ROLE_USER")',
        ),
    ],
    normalizationContext: ['groups' => ['club_membership:read']],
    denormalizationContext: ['groups' => ['club_membership:write']],
)]
class ClubMembership
{
    #[Groups(['club_membership:write'])]
    #[Assert\NotBlank]
    public ?string $clubCode = null;

    #[Groups(['club_membership:read'])]
    public ?int $userClubId = null;

    #[Groups(['club_membership:read'])]
    public string $message = 'Votre demande d\'inscription a bien été enregistrée.';
}