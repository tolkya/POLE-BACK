<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\SkillMediaTuto;
use App\Entity\User;
use App\Repository\SkillRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class SkillMediaTutoProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SkillRepository $skillRepository,
        private readonly Security $security,
        private readonly RequestStack $requestStack,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): SkillMediaTuto
    {
        $skill = $this->skillRepository->find($uriVariables['skillId']);
        if ($skill === null) {
            throw new NotFoundHttpException('Compétence introuvable.');
        }

        /** @var User $currentUser */
        $currentUser = $this->security->getUser();

        $club = $skill->getLevel()->getActivity()->getClub();
        $isAdmin = $this->security->isGranted('CLUB_ADMIN', $club);

        if (!$isAdmin) {
            if ($skill->getCreatedBy()?->getId() !== $currentUser->getId()) {
                throw new AccessDeniedHttpException('Vous ne pouvez ajouter des tutos qu\'à vos propres compétences.');
            }
        }

        /** @var UploadedFile|null $uploadedFile */
        $uploadedFile = $this->requestStack->getCurrentRequest()?->files->get('file');
        if ($uploadedFile === null) {
            throw new BadRequestHttpException('Aucun fichier reçu.');
        }

        $tuto = new SkillMediaTuto();
        $tuto->setSkill($skill);
        $tuto->setCreatedBy($currentUser);
        $tuto->setFile($uploadedFile);

        $this->em->persist($tuto);
        $this->em->flush();

        return $tuto;
    }
}
