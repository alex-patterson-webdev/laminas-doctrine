<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Service\Configuration;

use Arp\LaminasDoctrine\Service\Configuration\ConfigurationFactory;
use Arp\LaminasFactory\AbstractFactory;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Psr\Container\ContainerInterface;

final class ConfigurationFactoryFactory extends AbstractFactory
{
    /**
     * @param ContainerInterface&ServiceLocatorInterface $container
     * @param string                                     $requestedName
     * @param array<string, mixed>|null                  $options
     *
     * @return ConfigurationFactory
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array $options = null
    ): ConfigurationFactory {
        return new ConfigurationFactory($container);
    }
}
