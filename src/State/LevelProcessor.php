<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Level;
use App\Entity\User;
use App\Repository\ActivityRepository;
use App\Repository\UserActivityRepository;
use App\Repository\UserClubRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class LevelProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ActivityRepository $activityRepository,
        private readonly UserClubRepository $userClubRepository,
        private readonly UserActivityRepository $userActivityRepository,
        private readonly Security $security,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Level
    {
        $activity = $this->activityRepository->find($uriVariables['activityId']);
        if ($activity === null) {
            throw new NotFoundHttpException('Activité introuvable.');
        }

        /** @var User $currentUser */
        $currentUser = $this->security->getUser();

        $club = $activity->getClub();
        $isAdmin = $this->security->isGranted('CLUB_ADMIN', $club);

        if (!$isAdmin) {
            // Vérifier si l'utilisateur est TEACHER de cette activité
            $userActivity = $this->userActivityRepository->findOneBy([
                'member'   => $currentUser,
                'activity' => $activity,
            ]);
            $isTeacher = $userActivity !== null && $userActivity->getRole()->value === 'TEACHER';

            if (!$isTeacher) {
                throw new AccessDeniedHttpException('Seul l\'administrateur du club ou un professeur de l\'activité peut créer un niveau.');
            }
        }

        $level = new Level();
        $level->setActivity($activity);
        $level->setValue($data->getValue());
        $level->setCreatedBy($currentUser);
        if ($data->getDescription() !== null) {
            $level->setDescription($data->getDescription());
        }

        $this->em->persist($level);
        $this->em->flush();

        return $level;
    }
}