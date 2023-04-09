<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Console\Module;

use Arp\LaminasFactory\AbstractFactory;
use Arp\LaminasDoctrine\Console\Module\HelperManager;
use Laminas\ServiceManager\Exception\InvalidArgumentException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

final class HelperManagerFactory extends AbstractFactory
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array<mixed>|null $options
     *
     * @return HelperManager
     *
     * @throws InvalidArgumentException
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container, string $requestedName, array $options = null): HelperManager
    {
        $config = $options ?? $this->getApplicationOptions($container, 'arp_console_helper_manager');

        return new HelperManager($container, $config);
    }
}
