<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Config;

use Arp\LaminasDoctrine\Config\DoctrineConfig;
use Arp\LaminasDoctrineConfiguration\Config\ConfigurationConfigs;
use Arp\LaminasDoctrineConnection\Config\ConnectionConfigs;
use Arp\LaminasDoctrineEntityManager\Config\EntityManagerConfigs;
use Arp\LaminasFactory\AbstractFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Container\ContainerInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Config
 */
final class DoctrineConfigFactory extends AbstractFactory
{
    /**
     * @param ContainerInterface        $container
     * @param string                    $requestedName
     * @param array<string, mixed>|null $options
     *
     * @return DoctrineConfig
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
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
