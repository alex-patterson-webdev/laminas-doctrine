<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Config;

use Arp\LaminasDoctrine\Config\ConfigurationConfigs;
use Arp\LaminasFactory\AbstractFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Container\ContainerInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Config
 */
final class ConfigurationConfigsFactory extends AbstractFactory
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array<mixed>|null  $options
     *
     * @return ConfigurationConfigs
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array $options = null
    ): ConfigurationConfigs {
        $config = $this->getApplicationOptions($container, 'doctrine');

        return new ConfigurationConfigs($config['configuration'] ?? []);
    }
}
