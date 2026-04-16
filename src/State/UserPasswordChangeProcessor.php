<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

final class UserPasswordChangeProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EntityManagerInterface $em,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $currentUser = $this->security->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException('Utilisateur non authentifié.');
        }

        $targetUser = $this->userRepository->find($uriVariables['id'] ?? null);
        if ($targetUser === null) {
            throw new NotFoundHttpException('Utilisateur introuvable.');
        }

        if ($targetUser !== $currentUser) {
            throw new AccessDeniedHttpException('Vous ne pouvez modifier que votre propre mot de passe.');
        }

        if (!$this->passwordHasher->isPasswordValid($targetUser, $data->currentPassword)) {
            throw new UnprocessableEntityHttpException('Le mot de passe actuel est incorrect.');
        }

        $targetUser->setPassword(
            $this->passwordHasher->hashPassword($targetUser, $data->plainPassword)
        );

        $this->em->flush();

        return null; // 204 No Content
    }
}