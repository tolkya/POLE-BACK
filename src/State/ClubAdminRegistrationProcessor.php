<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\ClubAdminRegistration;
use App\Entity\Club;
use App\Entity\User;
use App\Entity\UserClub;
use App\Repository\UserRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Gère la création complète d'un compte admin de club.
 *
 * Ordre des opérations :
 *  1. Créer le User (avec mot de passe hashé)
 *  2. Créer le Club
 *  3. Créer le UserClub → lien User ↔ Club avec rôle ADMIN, validatedAt null (en attente)
 *  4. Persister tout en BDD
 *  5. Envoyer une notification à tous les Super Admins
 *  6. Retourner le DTO enrichi avec userId et clubCode
 */
final class ClubAdminRegistrationProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly NotificationService $notificationService,
        private readonly UserRepository $userRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ClubAdminRegistration
    {
        // Vérifier que l'email n'est pas déjà utilisé — renvoie une 422 propre
        if ($this->userRepository->findOneBy(['email' => $data->email]) !== null) {
            throw new UnprocessableEntityHttpException('Cet email est déjà utilisé.');
        }

        // 1. Créer l'utilisateur
        $user = new User();
        $user->setEmail($data->email);
        $user->setFirstName($data->firstName);
        $user->setLastName($data->lastName);

        if ($data->phone !== null) {
            $user->setPhone($data->phone);
        }

        // Hasher le mot de passe en clair avant persistance
        $hashedPassword = $this->passwordHasher->hashPassword($user, $data->plainPassword);
        $user->setPassword($hashedPassword);

        // 2. Créer le club
        $club = new Club();
        $club->setName($data->clubName);

        // 3. Créer le lien UserClub avec rôle ADMIN
        // validatedAt = null → le Super Admin n'a pas encore validé ce club
        $userClub = new UserClub();
        $userClub->setMember($user);
        $userClub->setClub($club);
        $userClub->setRoles(['ADMIN']);
        $userClub->setValidatedAt(new \DateTimeImmutable());

        // 4. Persister en BDD (ordre important : user et club avant userClub)
        $this->em->persist($user);
        $this->em->persist($club);
        $this->em->persist($userClub);
        $this->em->flush();
        // Après le flush, $user->getId() et $club->getId() sont disponibles

        // 5. Notifier les Super Admins (après le flush pour avoir l'ID du club)
        $this->notificationService->notifyClubCreated($club, $user);

        // 6. Enrichir le DTO de réponse
        $data->userId = $user->getId();
        $data->clubCode = $club->getClubCode();

        return $data;
    }
}
