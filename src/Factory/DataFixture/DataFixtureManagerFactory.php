<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\DataFixture;

use Arp\LaminasDoctrine\Data\DataFixtureManager;
use Arp\LaminasFactory\AbstractFactory;
use Laminas\ServiceManager\Exception\InvalidArgumentException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

final class DataFixtureManagerFactory extends AbstractFactory
{
    /**
     * @throws InvalidArgumentException
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws ContainerExceptionInterface
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array $options = null
    ): DataFixtureManager {
        $options = $options ?? $this->getApplicationOptions($container, 'data_fixture_manager');

        return new DataFixtureManager($container, $options);
    }
}
