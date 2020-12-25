<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Service;

use Arp\LaminasDoctrine\Config\DoctrineConfig;
use Arp\LaminasDoctrine\Service\ConfigurationManager;
use Arp\LaminasDoctrine\Service\ConnectionManager;
use Arp\LaminasDoctrine\Service\Exception\ConfigurationManagerException;
use Arp\LaminasDoctrine\Service\Exception\ConnectionManagerException;
use Arp\LaminasFactory\AbstractFactory;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\ServiceManager;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Service
 */
final class EntityManagerFactory extends AbstractFactory
{
    /**
     * @noinspection PhpMissingParamTypeInspection
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return EntityManager
     *
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): EntityManager
    {
        /** @var DoctrineConfig $doctrineConfig */
        $doctrineConfig = $this->getService($container, DoctrineConfig::class, $requestedName);

        $entityManagerConfig = $options ?? $doctrineConfig->getEntityManagerConfig($requestedName);

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

        $configuration = $this->getConfiguration($container, $configuration, $requestedName);
        $connection = $this->getConnection($container, $connection, $requestedName);

        try {
            return EntityManager::create($connection, $configuration);
        } catch (\Throwable $e) {
            throw new ServiceNotCreatedException(
                sprintf('Failed to create entity manager instance \'%s\': %s', $requestedName, $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @param ContainerInterface|ServiceManager $container
     * @param Configuration|string|array        $configuration
     * @param string                            $serviceName
     *
     * @return Configuration
     *
     * @throws ServiceNotCreatedException
     */
    private function getConfiguration(ContainerInterface $container, $configuration, string $serviceName): Configuration
    {
        /** @var ConfigurationManager $configurationManager */
        $configurationManager = $container->get(ConfigurationManager::class);

        if (is_array($configuration)) {
            $configurationManager->addConfigurationConfig($serviceName, $configuration);
            $configuration = $serviceName;
        }

        if (is_string($configuration)) {
            $configuration = $this->loadConfiguration($configurationManager, $configuration, $serviceName);
        }

        if (!$configuration instanceof Configuration) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The configuration must be an object of type \'%s\'; \'%s\' provided for service \'%s\'',
                    Configuration::class,
                    (is_object($configuration) ? get_class($configuration) : gettype($configuration)),
                    $serviceName
                )
            );
        }

        return $configuration;
    }

    /**
     * @param ConfigurationManager $configurationManager
     * @param string               $name
     * @param string               $serviceName
     *
     * @return Configuration
     *
     * @throws ServiceNotCreatedException
     */
    private function loadConfiguration(
        ConfigurationManager $configurationManager,
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
                sprintf(
                    'Failed to load configuration \'%s\' for service \'%s\': %s',
                    $name,
                    $serviceName,
                    $e->getMessage()
                ),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Resolve the required Doctrine Connection instance to use from the provided $connection.
     *
     * @param ContainerInterface      $container
     * @param string|array|Connection $connection
     * @param string                  $serviceName
     *
     * @return Connection
     *
     * @throws ServiceNotCreatedException
     */
    private function getConnection(ContainerInterface $container, $connection, string $serviceName): Connection
    {
        /** @var ConnectionManager $connectionManager */
        $connectionManager = $container->get(ConnectionManager::class);

        if (is_array($connection)) {
            $connectionManager->addConnectionConfig($serviceName, $connection);
            $connection = $serviceName;
        }

        if (is_string($connection)) {
            $connection = $this->loadConnection($connectionManager, $connection, $serviceName);
        }

        if (!$connection instanceof Connection) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The connection must be an object of type \'%s\'; \'%s\' provided for service \'%s\'',
                    Connection::class,
                    (is_object($connection) ? get_class($connection) : gettype($connection)),
                    $serviceName
                )
            );
        }

        return $connection;
    }

    /**
     * @param ConnectionManager $connectionManager
     * @param string            $name
     * @param string            $serviceName
     *
     * @return Connection
     *
     * @throws ServiceNotCreatedException
     */
    private function loadConnection(ConnectionManager $connectionManager, string $name, string $serviceName): Connection
    {
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
                sprintf(
                    'Failed to load connection \'%s\' for service \'%s\': %s',
                    $name,
                    $serviceName,
                    $e->getMessage()
                ),
                $e->getCode(),
                $e
            );
        }
    }
}
