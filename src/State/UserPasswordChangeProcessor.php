<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\UserPasswordChange;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;

final class UserPasswordChangeProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EntityManagerInterface $em,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $currentUser = $this->security->getUser();

        // Récupère le user ciblé par l'URL /users/{id}
        $targetUser = $this->userRepository->find($uriVariables['id']);
        if ($targetUser === null) {
            throw new NotFoundHttpException('Utilisateur introuvable.');
        }

        // Self-service strict : on ne peut changer que son propre mot de passe
        if ($targetUser !== $currentUser) {
            throw new AccessDeniedHttpException('Vous ne pouvez modifier que votre propre mot de passe.');
        }

        // Vérifie que le mot de passe actuel est correct
        if (!$this->passwordHasher->isPasswordValid($targetUser, $data->currentPassword)) {
            throw new UnprocessableEntityHttpException('Le mot de passe actuel est incorrect.');
        }

        // Hash et sauvegarde le nouveau mot de passe
        $targetUser->setPassword(
            $this->passwordHasher->hashPassword($targetUser, $data->plainPassword)
        );

        $this->em->flush();

        return ['message' => 'Mot de passe mis à jour avec succès.'];
    }
}