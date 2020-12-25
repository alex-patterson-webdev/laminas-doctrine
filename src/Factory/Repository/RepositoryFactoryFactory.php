<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Repository;

use Arp\LaminasDoctrine\Repository\RepositoryFactory as RepositoryFactoryService;
use Arp\LaminasDoctrine\Repository\RepositoryManager;
use Arp\LaminasFactory\AbstractFactory;
use Arp\LaminasFactory\Exception\ServiceNotCreatedException;
use Arp\LaminasFactory\Exception\ServiceNotFoundException;
use Doctrine\ORM\Repository\DefaultRepositoryFactory;
use Interop\Container\ContainerInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Repository
 */
final class RepositoryFactoryFactory extends AbstractFactory
{
    /**
     * @noinspection PhpMissingParamTypeInspection
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return RepositoryFactoryService
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ): RepositoryFactoryService {
        /** @var RepositoryManager $repositoryManager */
        $repositoryManager = $this->getService($container, RepositoryManager::class, $requestedName);

        return new RepositoryFactoryService($repositoryManager, new DefaultRepositoryFactory());
    }
}
