<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Config;

use Arp\LaminasDoctrine\Config\EntityManagerConfigs;
use Arp\LaminasFactory\AbstractFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

final class EntityManagerConfigsFactory extends AbstractFactory
{
    /**
     * @param array<mixed>|null $options
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array $options = null
    ): EntityManagerConfigs {
        $configs = $this->getApplicationOptions($container, 'doctrine');

        return new EntityManagerConfigs($configs['entitymanager'] ?? []);
    }
}
