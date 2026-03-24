<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Level;
use App\Repository\ActivityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class LevelProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ActivityRepository $activityRepository,
        private readonly Security $security,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Level
    {
        $activity = $this->activityRepository->find($uriVariables['activityId']);
        if ($activity === null) {
            throw new NotFoundHttpException('Activité introuvable.');
        }

        if (!$this->security->isGranted('CLUB_ADMIN', $activity->getClub())) {
            throw new AccessDeniedHttpException('Seul l\'administrateur du club peut créer un niveau.');
        }

        $level = new Level();
        $level->setActivity($activity);
        $level->setValue($data->getValue());
        if ($data->getDescription() !== null) {
            $level->setDescription($data->getDescription());
        }

        $this->em->persist($level);
        $this->em->flush();

        return $level;
    }
}