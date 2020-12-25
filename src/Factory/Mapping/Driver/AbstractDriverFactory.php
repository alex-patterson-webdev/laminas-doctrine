<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Mapping\Driver;

use Arp\LaminasDoctrine\Config\DoctrineConfig;
use Arp\LaminasFactory\AbstractFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Mapping\Driver
 */
abstract class AbstractDriverFactory extends AbstractFactory
{
    /**
     * @var array
     */
    protected array $defaultOptions = [];

    /**
     * @param ContainerInterface $container
     * @param string             $driverName
     * @param array|null         $options
     *
     * @return array
     *
     * @throw ServiceNotCreatedException
     */
    protected function getOptions(ContainerInterface $container, string $driverName, ?array $options = null): array
    {
        if (null === $options) {
            /** @var DoctrineConfig $doctrineConfig */
            $doctrineConfig = $container->get(DoctrineConfig::class);

            if (!$doctrineConfig->hasDriverConfig($driverName)) {
                throw new ServiceNotCreatedException(
                    sprintf('Unable to find driver configuration for \'%s\'', $driverName)
                );
            }

            $options = $doctrineConfig->getDriverConfig($driverName);
        }

        return array_replace_recursive($this->defaultOptions, $options);
    }
}
