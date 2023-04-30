<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Service\Connection;

use Arp\LaminasDoctrine\Config\ConnectionConfigs;
use Arp\LaminasDoctrine\Service\Connection\ConnectionFactoryInterface;
use Arp\LaminasDoctrine\Service\Connection\ConnectionManager;
use Arp\LaminasFactory\AbstractFactory;
use Doctrine\DBAL\Connection;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

final class ConnectionManagerFactory extends AbstractFactory
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
    ): ConnectionManager {
        $options = $options ?? $this->getServiceOptions($container, $requestedName);

        /** @var ConnectionConfigs $configs */
        $configs = $this->getService($container, ConnectionConfigs::class, $requestedName);

        /** @var ConnectionFactoryInterface $connectionFactory */
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

        return new ConnectionManager($configs, $connectionFactory, $connections);
    }
}
