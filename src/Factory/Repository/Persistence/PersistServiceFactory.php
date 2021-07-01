<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Repository\Persistence;

use Arp\DoctrineEntityRepository\Persistence\PersistService;
use Arp\DoctrineEntityRepository\Persistence\PersistServiceInterface;
use Arp\LaminasDoctrine\Factory\Service\EntityManagerFactoryProviderTrait;
use Arp\LaminasFactory\AbstractFactory;
use Arp\LaminasMonolog\Factory\FactoryLoggerProviderTrait;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Repository\Persistence
 */
final class PersistServiceFactory extends AbstractFactory
{
    use EntityManagerFactoryProviderTrait;
    use FactoryLoggerProviderTrait;

    /**
     * @param ContainerInterface&ServiceLocatorInterface $container
     * @param string                                     $requestedName
     * @param array<mixed>|null                          $options
     *
     * @return PersistServiceInterface
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array $options = null
    ): PersistServiceInterface {
        $options = array_replace_recursive($this->getServiceOptions($container, $requestedName), $options ?? []);

        $entityName = $options['entity_name'] ?? null;
        if (empty($entityName)) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The required \'entity_name\' configuration option is missing for service \'%s\'',
                    $requestedName
                )
            );
        }

        $entityManager = $options['entity_manager'] ?? null;
        if (empty($entityManager)) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The required \'entity_manager\' configuration option is missing for service \'%s\'',
                    $requestedName
                )
            );
        }

        return new PersistService(
            $entityName,
            $this->getEntityManager($container, $entityManager, $requestedName),
            $this->getEventDispatcher($container, $options['event_dispatcher'] ?? [], $requestedName),
            $this->getLogger($container, $options['logger'] ?? null, $requestedName)
        );
    }

    /**
     * @param ServiceLocatorInterface                      $container
     * @param EventDispatcherInterface|string|array<mixed> $eventDispatcher
     * @param string                                       $serviceName
     *
     * @return EventDispatcherInterface
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     */
    private function getEventDispatcher(
        ServiceLocatorInterface $container,
        $eventDispatcher,
        string $serviceName
    ): EventDispatcherInterface {
        if (is_string($eventDispatcher)) {
            $eventDispatcher = $this->getService($container, $eventDispatcher, $serviceName);
        }

        if (is_array($eventDispatcher)) {
            $eventDispatcher = $this->buildService($container, 'EntityEventDispatcher', $eventDispatcher, $serviceName);
        }

        if (!$eventDispatcher instanceof EventDispatcherInterface) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The event dispatcher must be an object of type \'%s\'; \'%s\' provided for service \'%s\'',
                    EventDispatcherInterface::class,
                    is_object($eventDispatcher) ? get_class($eventDispatcher) : gettype($eventDispatcher),
                    $serviceName
                )
            );
        }

        return $eventDispatcher;
    }
}
