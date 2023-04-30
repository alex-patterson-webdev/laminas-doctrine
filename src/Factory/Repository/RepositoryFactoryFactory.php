<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Repository;

use Arp\LaminasDoctrine\Repository\RepositoryFactory as RepositoryFactoryService;
use Arp\LaminasDoctrine\Repository\RepositoryManager;
use Arp\LaminasFactory\AbstractFactory;
use Doctrine\ORM\Repository\DefaultRepositoryFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

final class RepositoryFactoryFactory extends AbstractFactory
{
    /**
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array $options = null
    ): RepositoryFactoryService {
        /** @var RepositoryManager $repositoryManager */
        $repositoryManager = $this->getService($container, RepositoryManager::class, $requestedName);

        return new RepositoryFactoryService($repositoryManager, new DefaultRepositoryFactory());
    }
}
