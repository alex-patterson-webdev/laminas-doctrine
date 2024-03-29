<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Service\Configuration;

use Arp\LaminasDoctrine\Config\ConfigurationConfigs;
use Arp\LaminasDoctrine\Service\Configuration\ConfigurationFactory;
use Arp\LaminasDoctrine\Service\Configuration\ConfigurationFactoryInterface;
use Arp\LaminasDoctrine\Service\Configuration\ConfigurationManager;
use Arp\LaminasFactory\AbstractFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Service
 */
final class ConfigurationManagerFactory extends AbstractFactory
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array<mixed>|null  $options
     *
     * @return ConfigurationManager
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array $options = null
    ): ConfigurationManager {
        /** @var ConfigurationConfigs $configurationConfigs */
        $configurationConfigs = $this->getService($container, ConfigurationConfigs::class, $requestedName);

        /** @var ConfigurationFactoryInterface $configurationFactory */
        $configurationFactory = $this->getService($container, ConfigurationFactory::class, $requestedName);

        return new ConfigurationManager($configurationFactory, $configurationConfigs);
    }
}
