<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Repository;

use Arp\DoctrineEntityRepository\EntityRepositoryInterface;
use Arp\DoctrineEntityRepository\EntityRepositoryProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Repository\RepositoryFactory as RepositoryFactoryInterface;
use Doctrine\Persistence\ObjectRepository;

/**
 * @deprecated
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Repository
 */
final class RepositoryFactory implements RepositoryFactoryInterface
{
    /**
     * @var EntityRepositoryProviderInterface
     */
    private EntityRepositoryProviderInterface $repositoryProvider;

    /**
     * The default (fallback) repository factory.
     *
     * @var RepositoryFactoryInterface
     */
    private RepositoryFactoryInterface $repositoryFactory;

    /**
     * @param EntityRepositoryProviderInterface $repositoryProvider
     * @param RepositoryFactoryInterface        $repositoryFactory
     */
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
     * @return EntityRepositoryInterface|ObjectRepository<object>
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
