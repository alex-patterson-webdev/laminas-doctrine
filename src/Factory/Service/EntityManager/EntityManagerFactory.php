<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Service\EntityManager;

use Arp\LaminasDoctrine\Config\EntityManagerConfigs;
use Arp\LaminasDoctrine\Service\Configuration\ConfigurationManagerInterface;
use Arp\LaminasDoctrine\Service\Configuration\Exception\ConfigurationManagerException;
use Arp\LaminasDoctrine\Service\Connection\ConnectionManagerInterface;
use Arp\LaminasDoctrine\Service\Connection\Exception\ConnectionManagerException;
use Arp\LaminasFactory\AbstractFactory;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

final class EntityManagerFactory extends AbstractFactory
{
    /**
     * @param ContainerInterface&ServiceLocatorInterface $container
     * @param array<string, mixed>|null $options
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container, string $requestedName, array $options = null): EntityManager
    {
        /** @var EntityManagerConfigs $configs */
        $configs = $this->getService($container, EntityManagerConfigs::class, $requestedName);

        $entityManagerConfig = $options ?? $configs->getEntityManagerConfig($requestedName);

        $connection = $entityManagerConfig['connection'] ?? null;
        if (null === $connection) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The required \'connection\' configuration option is missing for service \'%s\'',
                    $requestedName
                )
            );
        }

        $configuration = $entityManagerConfig['configuration'] ?? null;
        if (null === $configuration) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The required \'configuration\' configuration option is missing for service \'%s\'',
                    $requestedName
                )
            );
        }

        $eventManager = $entityManagerConfig['event_manager'] ?? null;
        try {
            return new EntityManager(
                $this->getConnection($container, $connection, $requestedName),
                $this->getConfiguration($container, $configuration, $requestedName),
                $this->getEventManager($container, $eventManager, $requestedName),
            );
        } catch (\Throwable $e) {
            throw new ServiceNotCreatedException(
                sprintf('Failed to create entity manager instance \'%s\': %s', $requestedName, $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @param string|array<string, mixed>|Connection $connection
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     */
    private function getConnection(
        ContainerInterface $container,
        string|array|Connection $connection,
        string $serviceName
    ): Connection {
        if ($connection instanceof Connection) {
            return $connection;
        }

        /** @var ConnectionManagerInterface $connectionManager */
        $connectionManager = $this->getService($container, ConnectionManagerInterface::class, $serviceName);

        if (is_array($connection)) {
            $connectionManager->addConnectionConfig($serviceName, $connection);
            $connection = $serviceName;
        }

        return $this->loadConnection($connectionManager, $connection, $serviceName);
    }

    /**
     * @throws ServiceNotCreatedException
     */
    private function loadConnection(
        ConnectionManagerInterface $connectionManager,
        string $name,
        string $serviceName
    ): Connection {
        if (!$connectionManager->hasConnection($name)) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'Failed to load connection \'%s\' for service \'%s\': '
                    . 'The connection has not been registered with the connection manager',
                    $name,
                    $serviceName
                )
            );
        }

        try {
            return $connectionManager->getConnection($name);
        } catch (ConnectionManagerException $e) {
            throw new ServiceNotCreatedException(
                sprintf('Failed to load connection \'%s\' for service \'%s\'', $name, $serviceName),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @param Configuration|string|array<string, mixed> $configuration
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     */
    private function getConfiguration(
        ServiceLocatorInterface $container,
        string|array|Configuration $configuration,
        string $serviceName
    ): Configuration {
        if (is_object($configuration)) {
            return $configuration;
        }

        /** @var ConfigurationManagerInterface $configurationManager */
        $configurationManager = $this->getService($container, ConfigurationManagerInterface::class, $serviceName);

        if (is_array($configuration)) {
            $configurationManager->addConfigurationConfig($serviceName, $configuration);
            $configuration = $serviceName;
        }

        return $this->loadConfiguration($configurationManager, $configuration, $serviceName);
    }

    /**
     * @throws ServiceNotCreatedException
     */
    private function loadConfiguration(
        ConfigurationManagerInterface $configurationManager,
        string $name,
        string $serviceName
    ): Configuration {
        if (!$configurationManager->hasConfiguration($name)) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'Failed to load configuration \'%s\' for service \'%s\': '
                    . 'The configuration has not been registered with the configuration manager',
                    $name,
                    $serviceName
                )
            );
        }

        try {
            return $configurationManager->getConfiguration($name);
        } catch (ConfigurationManagerException $e) {
            throw new ServiceNotCreatedException(
                sprintf('Failed to load configuration \'%s\' for service \'%s\'', $name, $serviceName),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    private function getEventManager(
        ContainerInterface $container,
        string|EventManager|null $eventManager,
        string $serviceName
    ): EventManager {
        $eventManager ??= new EventManager();

        if (is_object($eventManager)) {
            return $eventManager;
        }

        $eventManager = $this->getService($container, $eventManager, $serviceName);

        if (!$eventManager instanceof EventManager) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The event manager must be an object of type \'%s\'; \'%s\' provided for service \'%s\'',
                    EventManager::class,
                    is_object($eventManager) ? get_class($eventManager) : gettype($eventManager),
                    $serviceName,
                ),
            );
        }

        return $eventManager;
    }
}
