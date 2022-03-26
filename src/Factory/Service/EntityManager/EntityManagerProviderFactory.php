<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Service\EntityManager;

use Arp\LaminasDoctrine\Config\EntityManagerConfigs;
use Arp\LaminasDoctrine\Service\EntityManager\EntityManagerContainer;
use Arp\LaminasDoctrine\Service\EntityManager\EntityManagerProvider;
use Arp\LaminasDoctrine\Service\EntityManager\EntityManagerProviderInterface;
use Arp\LaminasDoctrine\Service\EntityManager\Exception\EntityManagerProviderException;
use Arp\LaminasFactory\AbstractFactory;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Service
 */
final class EntityManagerProviderFactory extends AbstractFactory
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array<mixed>|null  $options
     *
     * @return EntityManagerProviderInterface
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array $options = null
    ): EntityManagerProviderInterface {
        /** @var EntityManagerConfigs $configs */
        $configs = $this->getService($container, EntityManagerConfigs::class, $requestedName);

        /** @var EntityManagerContainer $entityManagerManager */
        $entityManagerManager = $this->getService($container, EntityManagerContainer::class, $requestedName);

        /** @var EntityManagerInterface[] $entityManagers */
        $entityManagers = [];

        try {
            return new EntityManagerProvider($configs, $entityManagerManager, $entityManagers);
        } catch (EntityManagerProviderException $e) {
            throw new ServiceNotCreatedException(
                sprintf('Failed to create entity manager provider \'%s\': %s', $requestedName, $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }
}
