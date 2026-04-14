<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\State\UserPasswordChangeProcessor;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/users/{id}/change-password',
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            processor: UserPasswordChangeProcessor::class,
        ),
    ],
)]
class UserPasswordChange
{
    #[Assert\NotBlank(message: 'Le mot de passe actuel est requis.')]
    public ?string $currentPassword = null;

    #[Assert\NotBlank(message: 'Le nouveau mot de passe est requis.')]
    #[Assert\Length(min: 8, minMessage: 'Le mot de passe doit contenir au moins 8 caractères.')]
    #[Assert\Regex(pattern: '/[A-Z]/', message: 'Le mot de passe doit contenir au moins une majuscule.')]
    #[Assert\Regex(pattern: '/\d/', message: 'Le mot de passe doit contenir au moins un chiffre.')]
    #[Assert\Regex(pattern: '/[!@#$%^&*()_+\-=\[\]{};\':\"\\|,.<>\/?`~]/', message: 'Le mot de passe doit contenir au moins un symbole.')]
    public ?string $plainPassword = null;
}