<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Data;

use Arp\LaminasFactory\AbstractFactory;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Interop\Container\ContainerInterface;
use Laminas\Hydrator\HydratorInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;

/**
 * Factory class that will construct a class that extends AbstractHydratorFixture based on configuration options.
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Data
 */
final class HydratorFixtureFactory extends AbstractFactory
{
    /**
     * @noinspection PhpMissingParamTypeInspection
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return FixtureInterface
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): FixtureInterface
    {
        $options = $options ?? $this->getServiceOptions($container, $requestedName, 'data_fixtures');

        $className = $options['class_name'] ?? null;
        if (null === $className) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The required \'class_name\' configuration option is missing for service \'%s\'',
                    $requestedName
                )
            );
        }

        $hydrator = $options['hydrator'] ?? null;
        if (null === $hydrator) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The required \'hydrator\' configuration option is missing for service \'%s\'',
                    $requestedName
                )
            );
        }

        if (is_string($hydrator)) {
            $hydrator = $this->getService(
                $this->getService($container, 'HydratorManager', $requestedName),
                $hydrator,
                $requestedName
            );
        }

        if (!$hydrator instanceof HydratorInterface) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The \'hydrator\' must be an object of type \'%s\'; \'%s\' provided for service \'%s\'',
                    HydratorInterface::class,
                    is_object($hydrator) ? get_class($hydrator) : gettype($hydrator),
                    $requestedName
                )
            );
        }

        return new $className($hydrator, $options['data'] ?? []);
    }
}
