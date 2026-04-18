<?php

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\State\LevelReorderProcessor;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/activities/{activityId}/levels/reorder',
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            processor: LevelReorderProcessor::class,
            status: 204,
            output: false,
        ),
    ],
)]
class LevelReorder
{
    #[Assert\NotNull(message: 'Le tableau des IDs est requis.')]
    #[Assert\Type(type: 'array', message: 'levelIds doit être un tableau.')]
    public ?array $levelIds = null;
}