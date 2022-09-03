<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Repository;

use Arp\Entity\EntityInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Repository\RepositoryFactory as RepositoryFactoryInterface;
use Doctrine\Persistence\ObjectRepository;

final class RepositoryFactory implements RepositoryFactoryInterface
{
    private EntityRepositoryProviderInterface $repositoryProvider;

    private RepositoryFactoryInterface $repositoryFactory;

    public function __construct(
        EntityRepositoryProviderInterface $repositoryProvider,
        RepositoryFactoryInterface $repositoryFactory
    ) {
        $this->repositoryProvider = $repositoryProvider;
        $this->repositoryFactory = $repositoryFactory;
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param string                 $entityName
     *
     * @return EntityRepositoryInterface<EntityInterface>|ObjectRepository<object>
     *
     * @throws \Throwable
     */
    public function getRepository(EntityManagerInterface $entityManager, $entityName): ObjectRepository
    {
        if ($this->repositoryProvider->hasRepository($entityName)) {
            $options = [
                'entity_name'    => $entityName,
                'entity_manager' => $entityManager,
            ];

            return $this->repositoryProvider->getRepository($entityName, $options);
        }

        return $this->repositoryFactory->getRepository($entityManager, $entityName);
    }
}
