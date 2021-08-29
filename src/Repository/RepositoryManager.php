<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Repository;

use Arp\DoctrineEntityRepository\EntityRepositoryInterface;
use Arp\DoctrineEntityRepository\EntityRepositoryProviderInterface;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;

/**
 * @deprecated
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Repository
 */
final class RepositoryManager extends AbstractPluginManager implements EntityRepositoryProviderInterface
{
    /**
     * Whether or not to auto-add a FQCN as an invokable if it exists.
     *
     * @var bool
     */
    protected $autoAddInvokableClass = false;

    /**
     * @var string
     */
    protected $instanceOf = EntityRepositoryInterface::class;

    /**
     * @param string $entityName
     *
     * @return bool
     */
    public function hasRepository(string $entityName): bool
    {
        return $this->has($entityName);
    }

    /**
     * @param string               $entityName
     * @param array<string, mixed> $options
     *
     * @return EntityRepositoryInterface
     *
     * @throws InvalidServiceException
     * @throws ServiceNotFoundException
     */
    public function getRepository(string $entityName, array $options = []): EntityRepositoryInterface
    {
        return $this->get($entityName, $options);
    }
}
