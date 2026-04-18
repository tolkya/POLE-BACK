<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;

final class MobileLoginController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly RateLimiterFactory $loginLimiter,
    ) {}

    #[Route('/api/mobile/login', name: 'api_mobile_login', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $ip = $request->getClientIp() ?? 'unknown';
        $limiter = $this->loginLimiter->create($ip);

        if (!$limiter->consume(1)->isAccepted()) {
            throw new TooManyRequestsHttpException(null, 'Trop de tentatives de connexion. Veuillez patienter.');
        }

        try {
            $payload = $request->toArray();
        } catch (\JsonException) {
            throw new BadRequestHttpException('JSON invalide.');
        }

        $email = isset($payload['email']) ? trim((string) $payload['email']) : '';
        $password = isset($payload['password']) ? (string) $payload['password'] : '';

        if ($email === '' || $password === '') {
            throw new UnprocessableEntityHttpException('Les champs email et password sont obligatoires.');
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user instanceof User || !$this->passwordHasher->isPasswordValid($user, $password)) {
            throw new UnauthorizedHttpException('Bearer', 'Identifiants invalides.');
        }

        $token = $this->jwtManager->create($user);

        return $this->json([
            'token' => $token,
            'tokenType' => 'Bearer',
            'expiresIn' => 3600,
        ]);
    }
}