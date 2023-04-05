<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Repository\RepositoryFactory as RepositoryFactoryInterface;
use Doctrine\Persistence\ObjectRepository;

final class RepositoryFactory implements RepositoryFactoryInterface
{
    public function __construct(
        private readonly EntityRepositoryProviderInterface $repositoryProvider,
        private readonly RepositoryFactoryInterface $repositoryFactory
    ) {
    }

    /**
     * @throws \Exception
     */
    public function getRepository(EntityManagerInterface $entityManager, $entityName): ObjectRepository
    {
        if ($this->repositoryProvider->hasRepository($entityName)) {
            $options = [
                'entity_name' => $entityName,
                'entity_manager' => $entityManager,
            ];

            return $this->repositoryProvider->getRepository($entityName, $options);
        }

        return $this->repositoryFactory->getRepository($entityManager, $entityName);
    }
}
