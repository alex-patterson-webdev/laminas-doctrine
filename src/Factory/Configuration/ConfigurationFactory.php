<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Configuration;

use Arp\LaminasDoctrine\Config\DoctrineConfigInterface;
use Arp\LaminasFactory\AbstractFactory;
use Doctrine\Common\Cache\Cache;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Repository\DefaultRepositoryFactory;
use Doctrine\ORM\Repository\RepositoryFactory;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

final class ConfigurationFactory extends AbstractFactory
{
    /**
     * @var array<string, mixed>
     */
    private array $defaultOptions = [
        'repository_factory' => DefaultRepositoryFactory::class,
        'generate_proxies'   => false,
        'metadata_cache'     => 'array',
        'query_cache'        => 'array',
        'result_cache'       => 'array',
        'hydration_cache'    => 'array',
    ];

    /**
     * @param ContainerInterface&ServiceLocatorInterface $container
     * @param string $serviceName
     * @param array<mixed>|null $options
     *
     * @return Configuration
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     */
    public function __invoke(
        ContainerInterface $container,
        string $serviceName,
        array $options = null
    ): Configuration {
        $options = $this->getOptions($container, $serviceName, $options);

        $configuration = new Configuration();

        if (!array_key_exists('proxy_dir', $options)) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The required \'proxy_dir\' configuration option is missing for service \'%s\'',
                    $serviceName
                )
            );
        }

        if (!array_key_exists('proxy_namespace', $options)) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The required \'proxy_namespace\' configuration option is missing for service \'%s\'',
                    $serviceName
                )
            );
        }

        if (!array_key_exists('driver', $options)) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The required \'driver\' configuration option is missing for service \'%s\'',
                    $serviceName
                )
            );
        }

        $configuration->setEntityNamespaces($options['entity_namespaces'] ?? []);
        $configuration->setMetadataDriverImpl(
            $this->getMappingDriver($container, $options['driver'], $serviceName)
        );

        $configuration->setProxyDir($options['proxy_dir']);
        $configuration->setAutoGenerateProxyClasses($options['generate_proxies']);
        $configuration->setProxyNamespace($options['proxy_namespace']);

        $configuration->setMetadataCacheImpl($this->getCache($container, $options['metadata_cache'], $serviceName));
        $configuration->setQueryCacheImpl($this->getCache($container, $options['query_cache'], $serviceName));
        $configuration->setResultCacheImpl($this->getCache($container, $options['result_cache'], $serviceName));
        $configuration->setHydrationCacheImpl($this->getCache($container, $options['hydration_cache'], $serviceName));

        if (!empty($options['repository_factory'])) {
            $configuration->setRepositoryFactory(
                $this->getRepositoryFactory($container, $options['repository_factory'], $serviceName)
            );
        }

        if (!empty($options['types'])) {
            $this->registerCustomTypes($options['types']);
        }

        // @todo EntityResolver
        // @todo setNamingStrategy() and setQuoteStrategy()
        // @todo 2nd Level Cache
        // @todo setSQLLogger()

        return $configuration;
    }

    /**
     * @param ServiceLocatorInterface $container
     * @param string|array<mixed>|MappingDriver $driver
     * @param string $serviceName
     *
     * @return MappingDriver
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
     * @param ServiceLocatorInterface $container
     * @param string|array<mixed>|Cache $cache
     * @param string $serviceName
     *
     * @return Cache
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     */
    private function getCache(ServiceLocatorInterface $container, string|array|Cache $cache, string $serviceName): Cache
    {
        if (is_string($cache)) {
            /** @var DoctrineConfigInterface $doctrineConfig */
            $doctrineConfig = $this->getService($container, DoctrineConfigInterface::class, $serviceName);

            if (!$doctrineConfig instanceof DoctrineConfigInterface || !$doctrineConfig->hasCacheConfig($cache)) {
                throw new ServiceNotCreatedException(
                    sprintf(
                        'The cache configuration \'%s\' could not be found for service \'%s\'',
                        $cache,
                        $serviceName
                    )
                );
            }

            $cache = $doctrineConfig->getCacheConfig($cache);
        }

        if (is_array($cache)) {
            $cache = $this->buildService($container, Cache::class, $cache, $serviceName);
        }

        if (!$cache instanceof Cache) {
            throw new ServiceNotCreatedException(
                sprintf('The \'cache\' configuration must be an object of type \'%s\'', Cache::class)
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
     * @param ContainerInterface $container
     * @param string $serviceName
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
     * @param array<string, string> $types
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
