<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Mapping\Driver;

use Arp\LaminasDoctrine\Config\DoctrineConfigInterface;
use Arp\LaminasFactory\AbstractFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

abstract class AbstractDriverFactory extends AbstractFactory
{
    /**
     * @var array<mixed>
     */
    protected array $defaultOptions = [];

    /**
     * @param array<string, mixed>|null $options
     *
     * @return array<string, mixed>
     *
     * @throws ServiceNotCreatedException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getOptions(ContainerInterface $container, string $driverName, ?array $options = null): array
    {
        if (null === $options) {
            /** @var DoctrineConfigInterface $doctrineConfig */
            $doctrineConfig = $container->get(DoctrineConfigInterface::class);

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
