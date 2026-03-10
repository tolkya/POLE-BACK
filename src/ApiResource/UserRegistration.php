<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\State\UserRegistrationProcessor;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/register',
            processor: UserRegistrationProcessor::class,
        ),
    ],
    normalizationContext: ['groups' => ['user_reg:read']],
    denormalizationContext: ['groups' => ['user_reg:write']],
)]
class UserRegistration
{
    #[Groups(['user_reg:write'])]
    #[Assert\NotBlank]
    #[Assert\Email]
    public ?string $email = null;

    #[Groups(['user_reg:write'])]
    #[Assert\NotBlank]
    public ?string $firstName = null;

    #[Groups(['user_reg:write'])]
    #[Assert\NotBlank]
    public ?string $lastName = null;

    #[Groups(['user_reg:write'])]
    public ?string $phone = null;

    #[Groups(['user_reg:write'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 8)]
    public ?string $plainPassword = null;

    #[Groups(['user_reg:write'])]
    #[Assert\NotBlank]
    public ?string $clubCode = null;

    #[Groups(['user_reg:read'])]
    public ?int $userId = null;

    #[Groups(['user_reg:read'])]
    public string $message = 'Votre compte a été créé. Vous êtes bien inscrit au club.';
}