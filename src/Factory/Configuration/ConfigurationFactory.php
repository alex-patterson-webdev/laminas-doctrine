<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Configuration;

use Arp\LaminasDoctrine\Config\DoctrineConfigInterface;
use Arp\LaminasFactory\AbstractFactory;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Repository\DefaultRepositoryFactory;
use Doctrine\ORM\Repository\RepositoryFactory;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

final class ConfigurationFactory extends AbstractFactory
{
    /**
     * @var array<string, mixed>
     */
    private array $defaultOptions = [
        'repository_factory' => DefaultRepositoryFactory::class,
    ];

    /**
     * @param ContainerInterface&ServiceLocatorInterface $container
     * @param array<mixed>|null                          $options
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array $options = null
    ): Configuration {
        $options = $this->getOptions($container, $requestedName, $options);

        $configuration = new Configuration();

        if (!array_key_exists('proxy_dir', $options)) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The required \'proxy_dir\' configuration option is missing for service \'%s\'',
                    $requestedName
                )
            );
        }

        if (!array_key_exists('proxy_namespace', $options)) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The required \'proxy_namespace\' configuration option is missing for service \'%s\'',
                    $requestedName
                )
            );
        }

        if (!array_key_exists('driver', $options)) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The required \'driver\' configuration option is missing for service \'%s\'',
                    $requestedName
                )
            );
        }

        $configuration->setEntityNamespaces($options['entity_namespaces'] ?? []);
        $configuration->setMetadataDriverImpl(
            $this->getMappingDriver($container, $options['driver'], $requestedName)
        );

        $configuration->setProxyDir($options['proxy_dir']);
        $configuration->setAutoGenerateProxyClasses($options['generate_proxies']);
        $configuration->setProxyNamespace($options['proxy_namespace']);

        if (isset($options['metadata_cache'])) {
            $configuration->setMetadataCache($this->getCache($container, $options['metadata_cache'], $requestedName));
        }

        if (isset($options['query_cache'])) {
            $configuration->setQueryCache($this->getCache($container, $options['query_cache'], $requestedName));
        }

        if (isset($options['result_cache'])) {
            $configuration->setResultCache($this->getCache($container, $options['result_cache'], $requestedName));
        }

        if (isset($options['hydration_cache'])) {
            $configuration->setHydrationCache($this->getCache($container, $options['hydration_cache'], $requestedName));
        }

        if (!empty($options['repository_factory'])) {
            $configuration->setRepositoryFactory(
                $this->getRepositoryFactory($container, $options['repository_factory'], $requestedName)
            );
        }

        if (!empty($options['type'])) {
            $this->registerCustomTypes($options['type']);
        }

        // @todo EntityResolver
        // @todo setNamingStrategy() and setQuoteStrategy()
        // @todo 2nd Level Cache
        // @todo setSQLLogger()

        return $configuration;
    }

    /**
     * @param string|array<mixed>|MappingDriver $driver
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     */
    private function getMappingDriver(
        ServiceLocatorInterface $container,
        string|array|MappingDriver $driver,
        string $serviceName
    ): MappingDriver {
        if (is_string($driver)) {
            /** @var DoctrineConfigInterface $doctrineConfig */
            $doctrineConfig = $this->getService($container, DoctrineConfigInterface::class, $serviceName);

            if (!$doctrineConfig->hasDriverConfig($driver)) {
                throw new ServiceNotCreatedException(
                    sprintf(
                        'The driver configuration \'%s\' could not be found for service \'%s\'',
                        $driver,
                        $serviceName
                    )
                );
            }

            $driver = $doctrineConfig->getDriverConfig($driver);
        }

        if (is_array($driver)) {
            $driver = $this->buildService($container, MappingDriver::class, $driver, $serviceName);
        }

        if (!$driver instanceof MappingDriver) {
            throw new ServiceNotCreatedException(
                sprintf('The \'driver\' configuration must be an object of type \'%s\'', MappingDriver::class)
            );
        }

        return $driver;
    }

    /**
     * @param string|array<mixed>|CacheItemPoolInterface $cache
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     */
    private function getCache(
        ServiceLocatorInterface $container,
        string|array|CacheItemPoolInterface $cache,
        string $serviceName
    ): CacheItemPoolInterface {
        if (is_string($cache)) {
            $cache = $this->buildService($container, Cache::class, ['name' => $cache], $serviceName);
        }

        if ($cache instanceof DoctrineProvider) {
            $cache = $cache->getPool();
        }

        if (!$cache instanceof CacheItemPoolInterface) {
            throw new ServiceNotCreatedException(
                sprintf('The \'cache\' configuration must be an object of type \'%s\'', CacheItemPoolInterface::class)
            );
        }

        return $cache;
    }

    /**
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     */
    private function getRepositoryFactory(
        ContainerInterface $container,
        string|RepositoryFactory $factory,
        string $serviceName
    ): RepositoryFactory {
        if (is_string($factory)) {
            if ($container->has($factory)) {
                $factory = $this->getService($container, $factory, $serviceName);
            } elseif (class_exists($factory) && is_a($factory, RepositoryFactory::class, true)) {
                $factory = new $factory();
            }
        }

        if (!$factory instanceof RepositoryFactory) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The \'repository_factory\' configuration must be an object of type \'%s\'; '
                    . '\'%s\' provided for service \'%s\'',
                    RepositoryFactory::class,
                    is_object($factory) ? get_class($factory) : gettype($factory),
                    $serviceName
                )
            );
        }

        return $factory;
    }

    /**
     * @param array<mixed>|null $options
     *
     * @return array<mixed>
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     */
    private function getOptions(ContainerInterface $container, string $serviceName, ?array $options): array
    {
        if (null === $options) {
            /** @var DoctrineConfigInterface $doctrineConfig */
            $doctrineConfig = $this->getService($container, DoctrineConfigInterface::class, $serviceName);

            if (
                !$doctrineConfig instanceof DoctrineConfigInterface
                || !$doctrineConfig->hasConfigurationConfig($serviceName)
            ) {
                throw new ServiceNotCreatedException(
                    sprintf('Unable to find configuration for \'%s\'', $serviceName)
                );
            }

            $options = $doctrineConfig->getConfigurationConfig($serviceName);
        }

        return array_replace_recursive($this->defaultOptions, $options);
    }

    /**
     * @param array<string, class-string<Type>> $types
     *
     * @throws ServiceNotCreatedException
     */
    private function registerCustomTypes(array $types): void
    {
        foreach ($types as $typeName => $typeClassName) {
            try {
                if (Type::hasType($typeName)) {
                    Type::overrideType($typeName, $typeClassName);
                } else {
                    Type::addType($typeName, $typeClassName);
                }
            } catch (DBALException $e) {
                throw new ServiceNotCreatedException(
                    sprintf('The doctrine type \'%s\' could not be registered', $typeName),
                    $e->getCode(),
                    $e
                );
            }
        }
    }
}
