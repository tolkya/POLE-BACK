<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Club;
use App\Repository\ClubRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ClubLogoProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ClubRepository $clubRepository,
        private readonly Security $security,
        private readonly RequestStack $requestStack,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Club
    {
        $club = $this->clubRepository->find($uriVariables['id']);
        if ($club === null) {
            throw new NotFoundHttpException('Club introuvable.');
        }

        if (!$this->security->isGranted('CLUB_ADMIN', $club)) {
            throw new AccessDeniedHttpException('Seul un administrateur du club peut modifier le logo.');
        }

        /** @var UploadedFile|null $uploadedFile */
        $uploadedFile = $this->requestStack->getCurrentRequest()?->files->get('logoFile');
        if ($uploadedFile === null) {
            throw new BadRequestHttpException('Aucun fichier reçu. Envoyez le fichier dans le champ "logoFile".');
        }

        $club->setLogoFile($uploadedFile);

        $this->em->flush();

        return $club;
    }
}
