<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\ActivityTypeMedia;
use App\Entity\User;
use App\Repository\ActivityTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ActivityTypeMediaProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ActivityTypeRepository $activityTypeRepository,
        private readonly Security $security,
        private readonly RequestStack $requestStack,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ActivityTypeMedia
    {
        $activityType = $this->activityTypeRepository->find($uriVariables['activityTypeId']);
        if ($activityType === null) {
            throw new NotFoundHttpException('Type d\'activité introuvable.');
        }

        /** @var User $currentUser */
        $currentUser = $this->security->getUser();

        if (!$this->security->isGranted('ROLE_SUPER_ADMIN') && !$this->security->isGranted('ROLE_USER')) {
            throw new AccessDeniedHttpException('Accès refusé.');
        }

        /** @var UploadedFile|null $uploadedFile */
        $uploadedFile = $this->requestStack->getCurrentRequest()?->files->get('file');
        if ($uploadedFile === null) {
            throw new BadRequestHttpException('Aucun fichier reçu.');
        }

        $media = new ActivityTypeMedia();
        $media->setActivityType($activityType);
        $media->setCreatedBy($currentUser);
        $media->setFile($uploadedFile);

        $this->em->persist($media);
        $this->em->flush();

        return $media;
    }
}