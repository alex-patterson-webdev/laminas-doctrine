<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Hydrator\Strategy;

use Arp\Entity\EntityInterface;
use Arp\LaminasDoctrine\Hydrator\Strategy\HydratorStrategy;
use Arp\LaminasDoctrine\Repository\EntityRepositoryInterface;
use Arp\LaminasDoctrine\Repository\RepositoryManager;
use Arp\LaminasFactory\AbstractFactory;
use Laminas\Hydrator\HydratorPluginManager;
use Laminas\Hydrator\Strategy\Exception\InvalidArgumentException;
use Laminas\Hydrator\Strategy\HydratorStrategy as LaminasHydratorStrategy;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

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
     * @throws ContainerExceptionInterface
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

        /** @var EntityRepositoryInterface<EntityInterface> $repository */
        $repository = $this->getService($repositoryManager, $entityName, $requestedName);

        return new HydratorStrategy($repository, $laminasHydrator);
    }
}
