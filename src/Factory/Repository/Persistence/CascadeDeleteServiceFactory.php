<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Repository\Persistence;

use Arp\DoctrineEntityRepository\Persistence\CascadeDeleteService;
use Arp\LaminasFactory\AbstractFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Container\ContainerInterface;
use Psr\Log\NullLogger;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Repository\Persistence
 */
final class CascadeDeleteServiceFactory extends AbstractFactory
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array<mixed>|null  $options
     *
     * @return CascadeDeleteService
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array $options = null
    ): CascadeDeleteService {
        $options = $options ?? $this->getServiceOptions($container, $requestedName);

        $logger = $options['logger'] ?? NullLogger::class;

        return new CascadeDeleteService(
            $this->getService($container, $logger, $requestedName),
            $options['options'] ?? [],
            $options['collection_options'] ?? []
        );
    }
}
