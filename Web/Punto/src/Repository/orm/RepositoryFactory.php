<?php

namespace App\Repository\orm;

use App\Entity\Player;
use Doctrine\Deprecations\Deprecation;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RepositoryFactory implements \Doctrine\ORM\Repository\RepositoryFactory
{
    public function __construct(
        private ManagerRegistry $registry
    )
    {
    }

    public function getRepository(EntityManagerInterface $entityManager, $entityName)
    {
        $metadata            = $entityManager->getClassMetadata($entityName);
        $repositoryClassName = $metadata->customRepositoryClassName
            ?: $entityManager->getConfiguration()->getDefaultRepositoryClassName();

        $repository = new $repositoryClassName($entityManager, $metadata);
        if (! $repository instanceof EntityRepository) {
            Deprecation::trigger(
                'doctrine/orm',
                'https://github.com/doctrine/orm/pull/9533',
                'Configuring %s as repository class is deprecated because it does not extend %s.',
                $repositoryClassName,
                EntityRepository::class
            );
        }

        return $repository;
    }
}
