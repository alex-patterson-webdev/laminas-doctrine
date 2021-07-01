<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Hydrator\Strategy;

use Arp\LaminasDoctrine\Hydrator\Strategy\HydratorCollectionStrategy;
use Arp\LaminasDoctrine\Repository\RepositoryManager;
use Arp\LaminasFactory\AbstractFactory;
use Laminas\Hydrator\HydratorPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Container\ContainerInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Hydrator\Strategy
 */
final class HydratorCollectionStrategyFactory extends AbstractFactory
{
    /**
     * @param ContainerInterface        $container
     * @param string                    $requestedName
     * @param array<string, mixed>|null $options
     *
     * @return HydratorCollectionStrategy
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array $options = null
    ): HydratorCollectionStrategy {
        $options = $options ?? $this->getServiceOptions($container, $requestedName);

        $entityName = $options['entity_name'] ?? null;
        if (empty($entityName)) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The required \'entity_name\' configuration option is missing for service \'%s\'',
                    $requestedName
                )
            );
        }

        $fieldName = $options['field_name'] ?? null;
        if (empty($entityName)) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The required \'field_name\' configuration option is missing for service \'%s\'',
                    $requestedName
                )
            );
        }

        $hydrator = $options['hydrator'] ?? null;
        if (empty($hydrator)) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The required \'hydrator\' configuration option is missing for service \'%s\'',
                    $requestedName
                )
            );
        }

        /** @var RepositoryManager $repositoryManager */
        $repositoryManager = $this->getService($container, RepositoryManager::class, $requestedName);

        /** @var HydratorPluginManager $hydratorManager */
        $hydratorManager = $this->getService($container, 'HydratorManager', $requestedName);

        return new HydratorCollectionStrategy(
            $fieldName,
            $this->getService($repositoryManager, $entityName, $requestedName),
            $this->getService($hydratorManager, $hydrator, $requestedName)
        );
    }
}
