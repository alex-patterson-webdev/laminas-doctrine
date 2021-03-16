<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Service;

use Arp\LaminasDoctrine\Config\DoctrineConfig;
use Arp\LaminasDoctrine\Service\Connection\ConnectionFactory;
use Arp\LaminasDoctrine\Service\Connection\ConnectionFactoryInterface;
use Arp\LaminasDoctrine\Service\Connection\ConnectionManager;
use Arp\LaminasFactory\AbstractFactory;
use Doctrine\DBAL\Connection;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Service
 */
final class ConnectionManagerFactory extends AbstractFactory
{
    /**
     * @param ContainerInterface|ServiceLocatorInterface $container
     * @param string                                     $requestedName
     * @param array|null                                 $options
     *
     * @return ConnectionManager
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     * @noinspection PhpMissingParamTypeInspection
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ConnectionManager
    {
        $options = $options ?? $this->getServiceOptions($container, $requestedName);

        /** @var DoctrineConfig $doctrineConfig */
        $doctrineConfig = $this->getService($container, DoctrineConfig::class, $requestedName);

        /** @var ConnectionFactoryInterface|string $connectionFactory */
        $connectionFactory = $this->getService(
            $container,
            $options['factory'] ?? ConnectionFactoryInterface::class,
            $requestedName
        );

        $connections = [];
        if (!empty($options['connections'])) {
            foreach ($options['connections'] as $name => $connection) {
                if ($connection instanceof Connection) {
                    $connections[$name] = $connection;
                }
            }
        }

        return new ConnectionManager($doctrineConfig, $connectionFactory, $connections);
    }
}
