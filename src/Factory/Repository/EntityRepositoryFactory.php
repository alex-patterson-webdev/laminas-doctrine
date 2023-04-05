<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Repository;

use Arp\Entity\EntityInterface;
use Arp\LaminasDoctrine\Repository\EntityRepository;
use Arp\LaminasDoctrine\Repository\EntityRepositoryInterface;
use Arp\LaminasDoctrine\Repository\Persistence\PersistService;
use Arp\LaminasDoctrine\Repository\Persistence\PersistServiceInterface;
use Arp\LaminasDoctrine\Repository\Query\QueryService;
use Arp\LaminasDoctrine\Repository\Query\QueryServiceInterface;
use Arp\LaminasDoctrine\Repository\Query\QueryServiceManager;
use Arp\LaminasFactory\AbstractFactory;
use Arp\LaminasMonolog\Factory\FactoryLoggerProviderTrait;
use Laminas\ServiceManager\Exception\ContainerModificationsNotAllowedException;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class EntityRepositoryFactory extends AbstractFactory
{
    use FactoryLoggerProviderTrait;

    /**
     * The default configuration for all entity repositories
     *
     * @var array<mixed>
     */
    private array $defaultOptions = [
        'logger' => null,
        'query_service' => [
            'service_name' => QueryService::class,
            'logger' => null,
        ],
        'persist_service' => [
            'service_name' => PersistService::class,
            'logger' => null,
        ],
    ];

    /**
     * @param ContainerInterface&ServiceLocatorInterface $container
     * @param array<string, mixed>|null $options
     *
     * @return EntityRepositoryInterface<EntityInterface>
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array $options = null
    ): EntityRepositoryInterface {
        $options = array_replace_recursive(
            $this->defaultOptions,
            $this->getServiceOptions($container, $requestedName, 'repositories'),
            $options ?? []
        );

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

        $className = $this->resolveClassName($entityName, $options);

        return new $className(
            $entityName,
            $queryService,
            $persistService,
            $this->getLogger($container, $options['logger'] ?? null, $requestedName)
        );
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return class-string<EntityRepositoryInterface<EntityInterface>>
     */
    private function resolveClassName(string $entityName, array $options = []): string
    {
        $className = $options['class_name'] ?? EntityRepository::class;
        if (empty($options['class_name'])) {
            $generatedClassNames = [
                str_replace('Entity', 'Repository', $entityName) . 'Repository',
                str_replace('Entity', 'Entity\\Repository', $entityName) . 'Repository',
            ];
            foreach ($generatedClassNames as $generatedClassName) {
                if (
                    class_exists($generatedClassName, true)
                    && is_subclass_of($generatedClassName, EntityRepositoryInterface::class, true)
                ) {
                    return $generatedClassName;
                }
            }
        }

        return $className;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return PersistServiceInterface<EntityInterface>
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
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
     * @param array<string, mixed> $options
     *
     * @return QueryServiceInterface<EntityInterface>
     *
     * @throws ContainerExceptionInterface
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     * @throws ContainerModificationsNotAllowedException
     * @throws InvalidServiceException
     */
    private function getQueryService(
        ServiceLocatorInterface $container,
        string $entityName,
        array $options,
        string $serviceName
    ): QueryServiceInterface {
        /** @var QueryServiceManager $queryServiceManager */
        $queryServiceManager = $this->getService($container, QueryServiceManager::class, $serviceName);

        if ($queryServiceManager->has($entityName)) {
            return $queryServiceManager->get($entityName);
        }

        $options = array_replace_recursive($this->getServiceOptions($container, QueryService::class), $options);
        $options['entity_name'] ??= $entityName;

        $queryService = $this->buildService(
            $container,
            $options['service_name'] ?? QueryService::class,
            $options,
            $serviceName
        );

        $queryServiceManager->setService($entityName, $queryService);

        return $queryService;
    }

    /**
     * @param array<mixed> $defaultOptions
     */
    public function setDefaultOptions(array $defaultOptions): void
    {
        $this->defaultOptions = $defaultOptions;
    }
}
