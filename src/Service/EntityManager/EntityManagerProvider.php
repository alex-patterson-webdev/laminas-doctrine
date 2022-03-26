<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Service\EntityManager;

use Arp\LaminasDoctrine\Config\EntityManagerConfigs;
use Arp\LaminasDoctrine\Factory\Service\EntityManager\EntityManagerFactory;
use Arp\LaminasDoctrine\Service\ContainerInterface;
use Arp\LaminasDoctrine\Service\EntityManager\Exception\EntityManagerProviderException;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\ServiceManager\Exception\ContainerModificationsNotAllowedException;
use Psr\Container\ContainerExceptionInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Service
 */
final class EntityManagerProvider implements EntityManagerProviderInterface
{
    /**
     * @var EntityManagerConfigs
     */
    private EntityManagerConfigs $configs;

    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * @param EntityManagerConfigs                        $configs
     * @param ContainerInterface                          $container
     * @param array<string, EntityManagerInterface|array> $entityManagers
     *
     * @throws EntityManagerProviderException
     */
    public function __construct(
        EntityManagerConfigs $configs,
        ContainerInterface $container,
        array $entityManagers = []
    ) {
        $this->configs = $configs;
        $this->container = $container;

        $this->setEntityManagers($entityManagers);
    }

    /**
     * @param string $name
     *
     * @return EntityManagerInterface
     *
     * @throws \Arp\LaminasDoctrine\Service\EntityManager\Exception\EntityManagerProviderException
     */
    public function getEntityManager(string $name): EntityManagerInterface
    {
        try {
            if (!$this->container->has($name) && $this->configs->hasEntityManagerConfig($name)) {
                $this->container->setService(
                    $name,
                    $this->create($name, $this->configs->getEntityManagerConfig($name))
                );
            }

            if ($this->container->has($name)) {
                return $this->container->get($name);
            }
        } catch (EntityManagerProviderException $e) {
            throw $e;
        } catch (ContainerExceptionInterface $e) {
            throw new EntityManagerProviderException(
                sprintf('Failed retrieve entity manager \'%s\': %s', $name, $e->getMessage()),
                $e->getCode(),
                $e
            );
        }

        throw new EntityManagerProviderException(
            sprintf('Unable to find entity manager \'%s\'', $name)
        );
    }

    /**
     * @param string $name
     *
     * @return EntityManagerInterface
     *
     * @throws \Arp\LaminasDoctrine\Service\EntityManager\Exception\EntityManagerProviderException
     */
    public function refresh(string $name): EntityManagerInterface
    {
        $entityManager = $this->getEntityManager($name);

        if ($this->container->has($name)) {
            if ($entityManager->isOpen()) {
                $entityManager->close();
            }

            $entityManager = $this->create($name, $this->configs->getEntityManagerConfig($name));

            try {
                $this->container->setService($name, $entityManager);
            } catch (ContainerExceptionInterface $e) {
                throw new EntityManagerProviderException(
                    sprintf('Failed to set create service \'%s\': %s', $name, $e->getMessage()),
                    $e->getCode(),
                    $e
                );
            }
        }

        return $entityManager;
    }

    /**
     * Set the configuration options for a single entity manager with the provided $name
     *
     * @param string               $name
     * @param array<string, mixed> $config
     */
    public function setEntityManagerConfig(string $name, array $config): void
    {
        $this->configs->setEntityManagerConfig($name, $config);
    }

    /**
     * Check if the entity manager is registered with the provider
     *
     * @param string $name The name of the entity manager to check
     *
     * @return bool
     */
    public function hasEntityManager(string $name): bool
    {
        return $this->container->has($name) || $this->configs->hasEntityManagerConfig($name);
    }

    /**
     * @param string                 $name
     * @param EntityManagerInterface $entityManager
     *
     * @throws \Arp\LaminasDoctrine\Service\EntityManager\Exception\EntityManagerProviderException
     */
    public function setEntityManager(string $name, EntityManagerInterface $entityManager): void
    {
        try {
            $this->container->setService($name, $entityManager);
        } catch (ContainerModificationsNotAllowedException $e) {
            throw new EntityManagerProviderException(
                sprintf('Unable to set entity manager service \'%s\': %s', $name, $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @param array<string, EntityManagerInterface|array> $entityManagers
     *
     * @throws \Arp\LaminasDoctrine\Service\EntityManager\Exception\EntityManagerProviderException
     */
    public function setEntityManagers(array $entityManagers): void
    {
        foreach ($entityManagers as $name => $entityManager) {
            if (is_array($entityManager)) {
                $this->setEntityManagerConfig($name, $entityManager);
            } else {
                $this->setEntityManager($name, $entityManager);
            }
        }
    }

    /**
     * @param string               $name
     * @param array<string, mixed> $config
     * @param string|null          $factoryClassName
     *
     * @return EntityManagerInterface
     *
     * @throws \Arp\LaminasDoctrine\Service\EntityManager\Exception\EntityManagerProviderException
     */
    private function create(string $name, array $config, ?string $factoryClassName = null): EntityManagerInterface
    {
        // We must exclude calls from refresh() so we need to check
        if (!$this->container->has($name)) {
            $this->registerServiceFactory($name, $factoryClassName ?: EntityManagerFactory::class);
        }

        try {
            return $this->container->build($name, $config);
        } catch (ContainerExceptionInterface $e) {
            throw new EntityManagerProviderException(
                sprintf('Failed to create entity manager \'%s\' from configuration: %s', $name, $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Add a manual factory service entry for entity manager $name, so we do not need to explicitly define it each
     * time with the 'entity_manager_container'
     *
     * @param string $name
     * @param string $factoryClassName
     *
     * @throws \Arp\LaminasDoctrine\Service\EntityManager\Exception\EntityManagerProviderException
     */
    private function registerServiceFactory(string $name, string $factoryClassName): void
    {
        try {
            $this->container->setFactory($name, $factoryClassName);
        } catch (ContainerModificationsNotAllowedException $e) {
            throw new EntityManagerProviderException(
                sprintf(
                    'Unable to set entity manager factory service \'%s\': %s',
                    $factoryClassName,
                    $e->getMessage()
                ),
                $e->getCode(),
                $e
            );
        }
    }
}
