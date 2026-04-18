<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Repository\ActivityRepository;
use App\Repository\LevelRepository;
use App\Repository\UserActivityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class LevelReorderProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ActivityRepository     $activityRepository,
        private readonly LevelRepository        $levelRepository,
        private readonly UserActivityRepository $userActivityRepository,
        private readonly Security               $security,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): null
    {
        $activity = $this->activityRepository->find($uriVariables['activityId']);
        if ($activity === null) {
            throw new NotFoundHttpException('Activité introuvable.');
        }

        /** @var User $currentUser */
        $currentUser = $this->security->getUser();
        $club        = $activity->getClub();
        $isAdmin     = $this->security->isGranted('CLUB_ADMIN', $club);

        if (!$isAdmin) {
            $userActivity = $this->userActivityRepository->findOneBy([
                'member'   => $currentUser,
                'activity' => $activity,
            ]);
            $isTeacher = $userActivity !== null && $userActivity->getRole()->value === 'TEACHER';

            if (!$isTeacher) {
                throw new AccessDeniedHttpException('Seul l\'administrateur du club ou un professeur de l\'activité peut réordonner les niveaux.');
            }
        }

        $levelIds       = $data->levelIds;
        $existingLevels = $this->levelRepository->findByActivitySorted($activity->getId());

        // Vérifier que le nombre d'IDs correspond au nombre de levels de l'activité
        if (count($levelIds) !== count($existingLevels)) {
            throw new UnprocessableEntityHttpException('Le nombre d\'IDs ne correspond pas au nombre de niveaux de cette activité.');
        }

        // Indexer les levels existants par ID pour accès rapide
        $levelsById = [];
        foreach ($existingLevels as $level) {
            $levelsById[$level->getId()] = $level;
        }

        // Vérifier que tous les IDs reçus appartiennent bien à cette activité
        foreach ($levelIds as $id) {
            if (!isset($levelsById[$id])) {
                throw new UnprocessableEntityHttpException("Le niveau $id n'appartient pas à cette activité.");
            }
        }

        // Vérifier pas de doublons
        if (count(array_unique($levelIds)) !== count($levelIds)) {
            throw new UnprocessableEntityHttpException('Le tableau contient des IDs en doublon.');
        }

        // Appliquer les nouvelles positions (0, 1, 2, ...)
        foreach ($levelIds as $index => $id) {
            $levelsById[$id]->setPosition($index);
        }

        $this->em->flush();

        return null;
    }
}