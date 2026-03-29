<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\UserRegistration;
use App\Entity\User;
use App\Entity\UserClub;
use App\Repository\ClubRepository;
use App\Repository\UserRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserRegistrationProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UserRepository $userRepository,
        private readonly ClubRepository $clubRepository,
        private readonly NotificationService $notificationService,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): UserRegistration
    {
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
            $userClub->setValidatedAt(new \DateTimeImmutable());

            $this->em->persist($userClub);
            $this->em->flush();

            $this->notificationService->notifyMemberValidated($club, $user);
        }

        $this->em->flush();

        $data->userId = $user->getId();

        return $data;
    }
}