<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Repository;

use Arp\DoctrineEntityRepository\EntityRepository;
use Arp\DoctrineEntityRepository\EntityRepositoryInterface;
use Arp\DoctrineEntityRepository\Persistence\PersistService;
use Arp\DoctrineEntityRepository\Persistence\PersistServiceInterface;
use Arp\DoctrineEntityRepository\Query\QueryService;
use Arp\DoctrineEntityRepository\Query\QueryServiceInterface;
use Arp\LaminasFactory\AbstractFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Repository
 */
final class RepositoryFactory extends AbstractFactory
{
    /**
     * @param ServiceLocatorInterface   $container
     * @param string                    $requestedName
     * @param array<string, mixed>|null $options
     *
     * @return EntityRepositoryInterface
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     */
    public function __invoke(
        ServiceLocatorInterface $container,
        string $requestedName,
        array $options = null
    ): EntityRepositoryInterface {
        $options = $options ?? $this->getServiceOptions($container, $requestedName, 'repositories');

        $entityName = $options['entity_name'] ?? $requestedName;
        if (empty($entityName)) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The required \'entity_name\' configuration option is missing for service \'%s\'',
                    $requestedName
                )
            );
        }

        $queryService = $this->getQueryService(
            $container,
            $entityName,
            $options['query_service'] ?? [],
            $requestedName
        );

        $persistService = $this->getPersistService(
            $container,
            $entityName,
            $options['persist_service'] ?? [],
            $requestedName
        );

        $logger = $options['logger'] ?? NullLogger::class;
        if (is_string($logger)) {
            /** @var LoggerInterface|string $logger */
            $logger = $this->getService($container, $logger, $requestedName);
        }

        $className = $this->resolveClassName($entityName, $options);

        return new $className($entityName, $queryService, $persistService, $logger);
    }

    /**
     * @param string               $entityName
     * @param array<string, mixed> $options
     *
     * @return string
     */
    private function resolveClassName(string $entityName, array $options = []): string
    {
        $className = $options['class_name'] ?? EntityRepository::class;
        if (empty($options['class_name'])) {
            $generatedClassName = str_replace('Entity', 'Repository', $entityName) . 'Repository';
            if (
                class_exists($generatedClassName, true)
                && is_subclass_of($generatedClassName, EntityRepositoryInterface::class, true)
            ) {
                $className = $generatedClassName;
            }
        }

        return $className;
    }

    /**
     * @param ServiceLocatorInterface $container
     * @param string                  $entityName
     * @param array<string, mixed>    $options
     * @param string                  $serviceName
     *
     * @return PersistServiceInterface
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     */
    private function getPersistService(
        ServiceLocatorInterface $container,
        string $entityName,
        array $options,
        string $serviceName
    ): PersistServiceInterface {
        $options = array_replace_recursive(
            $this->getServiceOptions($container, PersistService::class),
            $options
        );
        $options['entity_name'] ??= $entityName;

        return $this->buildService(
            $container,
            $options['service_name'] ?? PersistService::class,
            $options,
            $serviceName
        );
    }

    /**
     * @param ServiceLocatorInterface $container
     * @param string                  $entityName
     * @param array<string, mixed>    $options
     * @param string                  $serviceName
     *
     * @return QueryServiceInterface
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     */
    private function getQueryService(
        ServiceLocatorInterface $container,
        string $entityName,
        array $options,
        string $serviceName
    ): QueryServiceInterface {
        $options = array_replace_recursive(
            $this->getServiceOptions($container, QueryService::class),
            $options
        );
        $options['entity_name'] ??= $entityName;

        return $this->buildService(
            $container,
            $options['service_name'] ?? QueryService::class,
            $options,
            $serviceName
        );
    }
}
