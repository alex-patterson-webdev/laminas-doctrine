<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Mapping\Driver;

use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class MappingDriverChainFactory extends AbstractDriverFactory
{
    /**
     * @param ContainerInterface&ServiceLocatorInterface $container
     * @param array<mixed>|null $options
     *
     * @throws ServiceNotCreatedException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array $options = null
    ): MappingDriverChain {
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
     * @param MappingDriver|string|array<mixed> $driver
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ServiceNotCreatedException
     */
    private function createDriver(
        ServiceLocatorInterface $container,
        MappingDriver|string|array $driver,
        string $serviceName
    ): MappingDriver {
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
