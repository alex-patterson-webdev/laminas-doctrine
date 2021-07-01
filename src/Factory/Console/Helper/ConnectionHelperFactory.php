<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Console\Helper;

use Arp\LaminasDoctrine\Console\Helper\ConnectionHelper;
use Arp\LaminasDoctrine\Factory\Service\EntityManagerFactoryProviderTrait;
use Arp\LaminasDoctrine\Factory\Service\ObjectManagerArgvInputProviderTrait;
use Arp\LaminasDoctrine\Service\Connection\ConnectionManagerInterface;
use Arp\LaminasDoctrine\Service\Connection\Exception\ConnectionManagerException;
use Arp\LaminasFactory\AbstractFactory;
use Doctrine\DBAL\Connection;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Container\ContainerInterface;
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
     * @param ContainerInterface        $container
     * @param string                    $requestedName
     * @param array<string, mixed>|null $options
     *
     * @return ConnectionHelper
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array $options = null
    ): ConnectionHelper {
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
     *
     * @throws ServiceNotCreatedException
     */
    private function resolveConnection(ContainerInterface $container, string $serviceName)
    {
        try {
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
        } catch (\Throwable $e) {
            throw new ServiceNotCreatedException(
                sprintf('Failed to resolve connection for service \'%s\': %s', $serviceName, $e->getMessage()),
                $e->getCode(),
                $e
            );
        }

        return null;
    }

    /**
     * @param ContainerInterface      $container
     * @param string|Connection|mixed $connection
     * @param string                  $serviceName
     *
     * @return Connection
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     */
    private function getConnection(ContainerInterface $container, $connection, string $serviceName): Connection
    {
        if (is_string($connection)) {
            $connectionName = $connection;

            /** @var ConnectionManagerInterface $connectionManager */
            $connectionManager = $this->getService($container, ConnectionManagerInterface::class, $serviceName);

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
