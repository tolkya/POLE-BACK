<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\ActivityMedia;
use App\Entity\User;
use App\Repository\ActivityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ActivityMediaProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ActivityRepository $activityRepository,
        private readonly Security $security,
        private readonly RequestStack $requestStack,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ActivityMedia
    {
        $activity = $this->activityRepository->find($uriVariables['activityId']);
        if ($activity === null) {
            throw new NotFoundHttpException('Activité introuvable.');
        }

        /** @var User $currentUser */
        $currentUser = $this->security->getUser();

        $club = $activity->getClub();
        if (!$this->security->isGranted('CLUB_ADMIN', $club) && !$this->security->isGranted('CLUB_TEACHER', $club)) {
            throw new AccessDeniedHttpException('Réservé aux admins et enseignants du club.');
        }

        /** @var UploadedFile|null $uploadedFile */
        $uploadedFile = $this->requestStack->getCurrentRequest()?->files->get('file');
        if ($uploadedFile === null) {
            throw new BadRequestHttpException('Aucun fichier reçu.');
        }

        $media = new ActivityMedia();
        $media->setActivity($activity);
        $media->setCreatedBy($currentUser);
        $media->setFile($uploadedFile);

        $this->em->persist($media);
        $this->em->flush();

        return $media;
    }
}