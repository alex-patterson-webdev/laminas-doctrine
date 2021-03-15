<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Service;

use Arp\LaminasDoctrine\Config\DoctrineConfig;
use Arp\LaminasDoctrine\Service\Configuration\ConfigurationManager;
use Arp\LaminasDoctrine\Service\Configuration\ConfigurationManagerInterface;
use Arp\LaminasDoctrine\Service\Connection\ConnectionFactory;
use Arp\LaminasFactory\AbstractFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Service
 */
final class ConnectionFactoryFactory extends AbstractFactory
{
    /**
     * @noinspection PhpMissingParamTypeInspection
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return ConnectionFactory
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ConnectionFactory
    {
        if (null === $options) {
            /** @var DoctrineConfig $doctrineConfig */
            $doctrineConfig = $this->getService($container, DoctrineConfig::class, $requestedName);

            $options = $doctrineConfig->hasConfigurationConfig($requestedName)
                ? $doctrineConfig->getConnectionConfig($requestedName)
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

        return new ConnectionFactory(
            $configurationManager,
            $connectionFactory,
            $this->factoryOptions['default_options'] ?? []
        );
    }
}
