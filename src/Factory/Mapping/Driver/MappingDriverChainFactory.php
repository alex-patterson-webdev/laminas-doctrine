<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Mapping\Driver;

use Arp\LaminasFactory\Exception\ServiceNotCreatedException;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\ServiceManager;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Mapping\Driver
 */
final class MappingDriverChainFactory extends AbstractDriverFactory
{
    /**
     * @noinspection PhpMissingParamTypeInspection
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return MappingDriverChain
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): MappingDriverChain
    {
        $options = $options ?? $this->getOptions($container, $requestedName, $options);

        $driverChain = new MappingDriverChain();

        if (!empty($options['drivers'])) {
            foreach ($options['drivers'] as $namespace => $driver) {
                if (empty($driver)) {
                    continue;
                }
                $driverChain->addDriver($this->createDriver($container, $driver, $requestedName), $namespace);
            }
        }

        return $driverChain;
    }

    /**
     * @param ContainerInterface|ServiceManager $container
     * @param MappingDriver|string|array        $driver
     * @param string                            $serviceName
     *
     * @return MappingDriver
     *
     * @throws ServiceNotCreatedException
     */
    private function createDriver(ContainerInterface $container, $driver, string $serviceName): MappingDriver
    {
        if (is_string($driver)) {
            $driver = $this->getOptions($container, $driver);
        }

        if (is_array($driver)) {
            if (empty($driver['class'])) {
                throw new ServiceNotCreatedException(
                    sprintf('The required \'class\' configuration option is missing for service \'%s\'', $serviceName),
                );
            }
            $driver = $this->buildService($container, $driver['class'], $driver, $serviceName);
        }

        if (!$driver instanceof MappingDriver) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The mapping driver must be an object of type \'%s\'; \'%s\' provided for service \'%s\'',
                    MappingDriver::class,
                    is_object($driver) ? get_class($driver) : gettype($driver),
                    $serviceName
                )
            );
        }

        return $driver;
    }
}
