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

final class ConfigurationManagerFactory extends AbstractFactory
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
    ): ConfigurationManager {
        /** @var ConfigurationConfigs $configurationConfigs */
        $configurationConfigs = $this->getService($container, ConfigurationConfigs::class, $requestedName);

        /** @var ConfigurationFactoryInterface $configurationFactory */
        $configurationFactory = $this->getService($container, ConfigurationFactory::class, $requestedName);

        return new ConfigurationManager($configurationFactory, $configurationConfigs);
    }
}
