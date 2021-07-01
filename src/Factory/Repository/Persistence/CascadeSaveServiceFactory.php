<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Repository\Persistence;

use Arp\DoctrineEntityRepository\Persistence\CascadeSaveService;
use Arp\LaminasFactory\AbstractFactory;
use Arp\LaminasMonolog\Factory\FactoryLoggerProviderTrait;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Psr\Container\ContainerInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Repository\Persistence
 */
final class CascadeSaveServiceFactory extends AbstractFactory
{
    use FactoryLoggerProviderTrait;

    /**
     * @param ContainerInterface&ServiceLocatorInterface $container
     * @param string                                     $requestedName
     * @param array<mixed>|null                          $options
     *
     * @return CascadeSaveService
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array $options = null
    ): CascadeSaveService {
        $options = $options ?? $this->getServiceOptions($container, $requestedName);

        return new CascadeSaveService(
            $this->getLogger($container, $options['logger'] ?? null, $requestedName),
            $options['options'] ?? [],
            $options['collection_options'] ?? []
        );
    }
}
