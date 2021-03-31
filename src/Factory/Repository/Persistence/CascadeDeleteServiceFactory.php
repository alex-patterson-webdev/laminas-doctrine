<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Repository\Persistence;

use Arp\DoctrineEntityRepository\Persistence\CascadeDeleteService;
use Arp\LaminasFactory\AbstractFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
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

        return new CascadeDeleteService(
            $this->getLogger($container, $options['logger'] ?? null, $requestedName),
            $options['options'] ?? [],
            $options['collection_options'] ?? []
        );
    }

    /**
     * @param ContainerInterface          $container
     * @param LoggerInterface|string|null $logger
     * @param string                      $serviceName
     *
     * @return LoggerInterface
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     */
    private function getLogger(ContainerInterface $container, $logger, string $serviceName): LoggerInterface
    {
        if (null === $logger) {
            return new NullLogger();
        }

        if (is_string($logger)) {
            $logger = $this->getService($container, $logger, $serviceName);
        }

        if (!$logger instanceof LoggerInterface) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The logger must be of type \'%s\'; \'%s\' provided for service \'%s\'',
                    LoggerInterface::class,
                    is_object($logger) ? get_class($logger) : gettype($logger),
                    $serviceName
                )
            );
        }

        return $logger;
    }
}
