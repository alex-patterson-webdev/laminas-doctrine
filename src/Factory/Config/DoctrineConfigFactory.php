<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Config;

use Arp\LaminasDoctrine\Config\DoctrineConfig;
use Arp\LaminasDoctrine\Config\ConfigurationConfigs;
use Arp\LaminasDoctrine\Config\ConnectionConfigs;
use Arp\LaminasDoctrine\Config\EntityManagerConfigs;
use Arp\LaminasFactory\AbstractFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

final class DoctrineConfigFactory extends AbstractFactory
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array<string, mixed>|null $options
     *
     * @return DoctrineConfig
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array $options = null
    ): DoctrineConfig {
        $options = $options ?? $this->getApplicationOptions($container, 'doctrine');

        /** @var EntityManagerConfigs $entityManagerConfigs */
        $entityManagerConfigs = $this->getService($container, EntityManagerConfigs::class, $requestedName);

        /** @var ConnectionConfigs $connectionConfigs */
        $connectionConfigs = $this->getService($container, ConnectionConfigs::class, $requestedName);

        /** @var ConfigurationConfigs $configurationConfigs */
        $configurationConfigs = $this->getService($container, ConfigurationConfigs::class, $requestedName);

        if (empty($options['driver'])) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The required \'driver\' configuration key is missing for service \'%s\'',
                    $requestedName
                )
            );
        }

        return new DoctrineConfig(
            $entityManagerConfigs,
            $connectionConfigs,
            $configurationConfigs,
            $options
        );
    }
}
