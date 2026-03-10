<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\State\ClubAdminRegistrationProcessor;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO (Data Transfer Object) pour l'inscription d'un créateur de club.
 *
 * Ce n'est PAS une entité Doctrine — pas de table en BDD pour cette classe.
 * API Platform l'utilise pour désérialiser le JSON entrant et valider les champs.
 * C'est le ClubAdminRegistrationProcessor qui crée les vraies entités (User, Club, UserClub).
 */
#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/register/club-admin',
            processor: ClubAdminRegistrationProcessor::class,
        ),
    ],
    normalizationContext: ['groups' => ['club_admin_reg:read']],
    denormalizationContext: ['groups' => ['club_admin_reg:write']],
)]
class ClubAdminRegistration
{
    // ─── Champs attendus en entrée (dans le JSON de la requête) ───────────────

    #[Groups(['club_admin_reg:write'])]
    #[Assert\NotBlank]
    #[Assert\Email]
    public ?string $email = null;

    #[Groups(['club_admin_reg:write'])]
    #[Assert\NotBlank]
    public ?string $firstName = null;

    #[Groups(['club_admin_reg:write'])]
    #[Assert\NotBlank]
    public ?string $lastName = null;

    #[Groups(['club_admin_reg:write'])]
    public ?string $phone = null;

    #[Groups(['club_admin_reg:write'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 8)]
    public ?string $plainPassword = null;

    #[Groups(['club_admin_reg:write'])]
    #[Assert\NotBlank]
    public ?string $clubName = null;

    // ─── Champs renvoyés en sortie (dans la réponse JSON) ────────────────────

    #[Groups(['club_admin_reg:read'])]
    public ?int $userId = null;

    #[Groups(['club_admin_reg:read'])]
    public ?string $clubCode = null;

    #[Groups(['club_admin_reg:read'])]
    public string $message = 'Votre club a été créé avec succès. Vous pouvez dès maintenant commencer à le gérer.';
}
