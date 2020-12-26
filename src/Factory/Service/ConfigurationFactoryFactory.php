<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Service;

use Arp\LaminasDoctrine\Service\Configuration\ConfigurationFactory;
use Arp\LaminasFactory\AbstractFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\ServiceManager;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Service
 */
final class ConfigurationFactoryFactory extends AbstractFactory
{
    /**
     * @param ContainerInterface|ServiceManager $container
     * @param string                            $requestedName
     * @param array|null                        $options
     *
     * @return ConfigurationFactory
     *
     * @noinspection PhpMissingParamTypeInspection
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ConfigurationFactory
    {
        return new ConfigurationFactory($container);
    }
}
