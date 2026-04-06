<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\UserRegistration;
use App\Entity\User;
use App\Entity\UserClub;
use App\Enum\JoinPolicy;
use App\Repository\ClubRepository;
use App\Repository\UserRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;

final class UserRegistrationProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UserRepository $userRepository,
        private readonly ClubRepository $clubRepository,
        private readonly NotificationService $notificationService,
        private readonly RateLimiterFactory $registerLimiter,
        private readonly RequestStack $requestStack,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): UserRegistration
    {
        $ip = $this->requestStack->getCurrentRequest()?->getClientIp() ?? 'unknown';
        $limiter = $this->registerLimiter->create($ip);
        if (!$limiter->consume(1)->isAccepted()) {
            throw new TooManyRequestsHttpException(null, 'Trop de tentatives d\'inscription. Veuillez patienter.');
        }

        if ($this->userRepository->findOneBy(['email' => $data->email]) !== null) {
            throw new ConflictHttpException('Cet email est déjà utilisé.');
        }

        $user = new User();
        $user->setEmail($data->email);
        $user->setFirstName($data->firstName);
        $user->setLastName($data->lastName);
        if ($data->phone !== null) {
            $user->setPhone($data->phone);
        }
        $user->setPassword($this->passwordHasher->hashPassword($user, $data->plainPassword));

        $this->em->persist($user);

        if ($data->clubCode !== null && $data->clubCode !== '') {
            $club = $this->clubRepository->findByClubCode($data->clubCode);
            if ($club === null) {
                throw new NotFoundHttpException('Code club invalide.');
            }

            $userClub = new UserClub();
            $userClub->setMember($user);
            $userClub->setClub($club);
            $userClub->setRoles(['MEMBER']);

            // Respect de la politique d'inscription du club
            $isAutoAccepted = $club->getJoinPolicy() === JoinPolicy::AUTO_ACCEPT->value;
            if ($isAutoAccepted) {
                $userClub->setValidatedAt(new \DateTimeImmutable());
            }

            $this->em->persist($userClub);
            $this->em->flush();

            if ($isAutoAccepted) {
                $this->notificationService->notifyMemberValidated($club, $user);
            }
        }

        $this->em->flush();

        $data->userId = $user->getId();

        return $data;
    }
}