<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Skill;
use App\Repository\LevelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class SkillProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly LevelRepository $levelRepository,
        private readonly Security $security,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Skill
    {
        $level = $this->levelRepository->find($uriVariables['levelId']);
        if ($level === null) {
            throw new NotFoundHttpException('Niveau introuvable.');
        }

        if (!$this->security->isGranted('SKILL_MANAGE', $level)) {
            throw new AccessDeniedHttpException('Vous n\'avez pas les droits pour gérer les compétences de ce niveau.');
        }

        $skill = new Skill();
        $skill->setLevel($level);
        $skill->setName($data->getName());
        if ($data->getDescription() !== null) {
            $skill->setDescription($data->getDescription());
        }

        $this->em->persist($skill);
        $this->em->flush();

        return $skill;
    }
}