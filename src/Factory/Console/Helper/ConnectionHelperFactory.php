<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Console\Helper;

use Arp\LaminasDoctrine\Console\Helper\ConnectionHelper;
use Arp\LaminasDoctrine\Factory\Service\EntityManagerFactoryProviderTrait;
use Arp\LaminasDoctrine\Factory\Service\ObjectManagerArgvInputProviderTrait;
use Arp\LaminasDoctrine\Service\ConnectionManager;
use Arp\LaminasDoctrine\Service\ConnectionManagerInterface;
use Arp\LaminasDoctrine\Service\Exception\ConnectionManagerException;
use Arp\LaminasFactory\AbstractFactory;
use Arp\LaminasFactory\Exception\ServiceNotCreatedException;
use Doctrine\DBAL\Connection;
use Interop\Container\ContainerInterface;
use Symfony\Component\Console\Input\ArgvInput;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Console\Helper
 */
final class ConnectionHelperFactory extends AbstractFactory
{
    use ObjectManagerArgvInputProviderTrait;
    use EntityManagerFactoryProviderTrait;

    /**
     * @noinspection PhpMissingParamTypeInspection
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return ConnectionHelper
     *
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ConnectionHelper
    {
        $options = $options ?? $this->getServiceOptions($container, $requestedName);

        if (empty($options['connection'])) {
            $options['connection'] = $this->resolveConnection($container, $requestedName);
        }

        if (empty($options['connection'])) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The required \'connection\' configuration option is missing for service \'%s\'',
                    $requestedName
                )
            );
        }

        return new ConnectionHelper(
            $this->getConnection($container, $options['connection'], $requestedName)
        );
    }

    /**
     * @param ContainerInterface $container
     * @param string             $serviceName
     *
     * @return Connection|string|null
     */
    private function resolveConnection(ContainerInterface $container, string $serviceName)
    {
        $arguments = new ArgvInput();

        // First check if we require a specific connection
        if ($arguments->hasOption('--connection')) {
            /** @var string $connectionName */
            $connectionName = $arguments->getOption('--connection');
            return $connectionName;
        }

        // Fall back to checking if we provided a --object-manager option
        $objectManagerName = $this->getEntityManagerArgvInput();
        if (!empty($objectManagerName)) {
            return $this->getEntityManager($container, $objectManagerName, $serviceName)->getConnection();
        }

        return null;
    }

    /**
     * @param ContainerInterface $container
     * @param string|Connection  $connection
     * @param string             $serviceName
     *
     * @return Connection
     *
     * @throws ServiceNotCreatedException
     */
    private function getConnection(ContainerInterface $container, $connection, string $serviceName): Connection
    {
        if (is_string($connection)) {
            $connectionName = $connection;

            /** @var ConnectionManagerInterface $connectionManager */
            $connectionManager = $this->getService($container, ConnectionManager::class, $serviceName);

            try {
                $connection = $connectionManager->getConnection($connection);
            } catch (ConnectionManagerException $e) {
                throw new ServiceNotCreatedException(
                    sprintf(
                        'The connection \'%s\' could not be found for service \'%s\'',
                        $connectionName,
                        $serviceName
                    ),
                    $e->getCode(),
                    $e
                );
            }
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
}
