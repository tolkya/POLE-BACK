<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Activity;
use App\Repository\ClubRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ActivityProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ClubRepository $clubRepository,
        private readonly Security $security,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Activity
    {
        $club = $this->clubRepository->find($uriVariables['clubId']);
        if ($club === null) {
            throw new NotFoundHttpException('Club introuvable.');
        }

        // Seul l'admin du club peut créer une activité
        if (!$this->security->isGranted('CLUB_ADMIN', $club)) {
            throw new AccessDeniedHttpException('Vous n\'êtes pas administrateur de ce club.');
        }

        $activity = new Activity();
        $activity->setClub($club);
        $activity->setName($data->getName());
        $activity->setActivityType($data->getActivityType());
        if ($data->getDescription() !== null) {
            $activity->setDescription($data->getDescription());
        }

        $this->em->persist($activity);
        $this->em->flush();

        return $activity;
    }
}