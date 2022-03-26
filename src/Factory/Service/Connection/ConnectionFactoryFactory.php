<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Service\Connection;

use Arp\LaminasDoctrine\Config\ConnectionConfigs;
use Arp\LaminasDoctrine\Service\Configuration\ConfigurationManager;
use Arp\LaminasDoctrine\Service\Configuration\ConfigurationManagerInterface;
use Arp\LaminasDoctrine\Service\Connection\ConnectionFactory;
use Arp\LaminasFactory\AbstractFactory;
use Doctrine\DBAL\Driver\PDO\MySQL\Driver;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Service
 */
final class ConnectionFactoryFactory extends AbstractFactory
{
    /**
     * @var array<string, mixed>
     */
    private array $defaultConnectionConfig = [
        'driverClass'   => Driver::class,
        'driverOptions' => null,
    ];

    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array<mixed>|null  $options
     *
     * @return ConnectionFactory
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array $options = null
    ): ConnectionFactory {
        if (null === $options) {
            /** @var ConnectionConfigs $connectionConfigs */
            $connectionConfigs = $this->getService($container, ConnectionConfigs::class, $requestedName);

            $options = $connectionConfigs->hasConnectionConfig($requestedName)
                ? $connectionConfigs->getConnectionConfig($requestedName)
                : [];
        }

        $connectionFactory = $options['factory'] ?? null;
        if (null !== $connectionFactory && !is_callable($connectionFactory)) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The \'factory\' must be of type \'callable\'; \'%s\' provided for service \'%s\'',
                    is_object($connectionFactory) ? get_class($connectionFactory) : gettype($connectionFactory),
                    $requestedName
                )
            );
        }

        /** @var ConfigurationManager $configurationManager */
        $configurationManager = $this->getService(
            $container,
            $options['manager'] ?? ConfigurationManagerInterface::class,
            $requestedName
        );

        return new ConnectionFactory($configurationManager, $connectionFactory, $this->defaultConnectionConfig);
    }

    /**
     * @param array<mixed> $defaultConnectionConfig
     */
    public function setDefaultConnectionConfig(array $defaultConnectionConfig): void
    {
        $this->defaultConnectionConfig = $defaultConnectionConfig;
    }
}
