<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Repository;

use Arp\LaminasDoctrine\Repository\RepositoryManager;
use Arp\LaminasFactory\AbstractFactory;
use Laminas\ServiceManager\Exception\InvalidArgumentException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Repository
 */
final class RepositoryManagerFactory extends AbstractFactory
{
    /**
     * @param ContainerInterface        $container
     * @param string                    $requestedName
     * @param array<string, mixed>|null $options
     *
     * @return RepositoryManager
     *
     * @throws InvalidArgumentException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws ContainerExceptionInterface
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array $options = null
    ): RepositoryManager {
        $config = $options ?? $this->getApplicationOptions($container, 'repository_manager');

        return new RepositoryManager($container, $config);
    }
}
