<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ClubRepository;
use App\Repository\NotificationReceiptRepository;
use App\Repository\UserRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class StatsController extends AbstractController
{
    #[Route('/api/stats', name: 'api_stats', methods: ['GET'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function stats(
        ClubRepository $clubRepository,
        UserRepository $userRepository,
        NotificationReceiptRepository $notificationReceiptRepository,
    ): JsonResponse {
        return $this->json([
            'clubs' => $clubRepository->count([]),
            'users' => $userRepository->count([]),
            'unreadNotifications' => $notificationReceiptRepository->countUnread(),
        ]);
    }
}
