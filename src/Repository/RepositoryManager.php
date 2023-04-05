<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Repository;

use Arp\Entity\EntityInterface;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;

/**
 * @extends AbstractPluginManager<EntityRepositoryInterface>
 */
final class RepositoryManager extends AbstractPluginManager implements EntityRepositoryProviderInterface
{
    protected $autoAddInvokableClass = false;

    /**
     * @var class-string<EntityRepositoryInterface<EntityInterface>>
     */
    protected $instanceOf = EntityRepositoryInterface::class;

    public function hasRepository(string $entityName): bool
    {
        return $this->has($entityName);
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return EntityRepositoryInterface<EntityInterface>
     *
     * @throws InvalidServiceException
     * @throws ServiceNotFoundException
     */
    public function getRepository(string $entityName, array $options = []): EntityRepositoryInterface
    {
        return $this->get($entityName, $options);
    }
}
