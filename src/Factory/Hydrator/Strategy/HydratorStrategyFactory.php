<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Hydrator\Strategy;

use Arp\DoctrineEntityRepository\EntityRepositoryInterface;
use Arp\LaminasDoctrine\Hydrator\Strategy\HydratorStrategy;
use Arp\LaminasDoctrine\Repository\RepositoryManager;
use Arp\LaminasFactory\AbstractFactory;
use Laminas\Hydrator\HydratorPluginManager;
use Laminas\Hydrator\Strategy\Exception\InvalidArgumentException;
use Laminas\Hydrator\Strategy\HydratorStrategy as LaminasHydratorStrategy;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Container\ContainerInterface;

/**
 * Create a hydrator strategy instance based on configuration options
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Hydrator\Strategy
 */
final class HydratorStrategyFactory extends AbstractFactory
{
    /**
     * @param ContainerInterface        $container
     * @param string                    $requestedName
     * @param array<string, mixed>|null $options
     *
     * @return HydratorStrategy
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     * @throws InvalidArgumentException
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array $options = null
    ): HydratorStrategy {
        $options = $options ?? $this->getServiceOptions($container, $requestedName);

        $entityName = $options['entity_name'] ?? null;
        if (null === $entityName) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The required \'entity_name\' configuration option is missing for service \'%s\'',
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

        /** @var HydratorPluginManager $hydratorManager */
        $hydratorManager = $this->getService($container, 'HydratorManager', $requestedName);

        $laminasHydrator = new LaminasHydratorStrategy(
            $this->getService($hydratorManager, $hydrator, $requestedName),
            $entityName
        );

        /** @var RepositoryManager $repositoryManager */
        $repositoryManager = $this->getService($container, RepositoryManager::class, $requestedName);

        /** @var EntityRepositoryInterface $repository */
        $repository = $this->getService($repositoryManager, $entityName, $requestedName);

        return new HydratorStrategy($repository, $laminasHydrator);
    }
}
