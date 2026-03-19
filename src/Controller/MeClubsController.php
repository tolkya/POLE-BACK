<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserClubRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class MeClubsController extends AbstractController
{
    #[Route('/api/me/clubs', name: 'api_me_clubs', methods: ['GET'])]
    public function __invoke(
        #[CurrentUser] User $user,
        UserClubRepository $userClubRepository,
    ): JsonResponse {
        $userClubs = $userClubRepository->findAllByUser($user);

        return $this->json($userClubs, 200, [], ['groups' => ['user_club:read']]);
    }
}
