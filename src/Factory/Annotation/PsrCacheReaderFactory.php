<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Annotation;

use Arp\LaminasDoctrine\Config\DoctrineConfigInterface;
use Arp\LaminasFactory\AbstractFactory;
use Doctrine\Common\Annotations\PsrCachedReader;
use Doctrine\Common\Annotations\Reader;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

final class PsrCacheReaderFactory extends AbstractFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array $options = null
    ): PsrCachedReader {
        $options = $options ?? $this->getServiceOptions($container, $requestedName);

        $reader = $options['reader'] ?? null;
        if (null === $reader) {
            throw new ServiceNotCreatedException(
                sprintf('The required \'reader\' configuration option is missing for service \'%s\'', $requestedName),
            );
        }

        $cache = $options['cache'] ?? null;
        if (null === $cache) {
            throw new ServiceNotCreatedException(
                sprintf('The required \'cache\' configuration option is missing for service \'%s\'', $requestedName),
            );
        }

        return new PsrCachedReader(
            $this->getReader($container, $reader, $requestedName),
            $this->getCache($container, $cache, $requestedName),
            (bool)($options['debug'] ?? false),
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     */
    private function getReader(ContainerInterface $container, Reader|string $reader, string $requestedName): Reader
    {
        if ($reader instanceof Reader) {
            return $reader;
        }

        $reader = $this->getService($container, $reader, $requestedName);
        if (!$reader instanceof Reader) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The reader must be an object of type \'%s\'; \'%s\' provided for service \'%s\'',
                    Reader::class,
                    is_object($reader) ? get_class($reader) : gettype($reader),
                    $requestedName,
                ),
            );
        }

        return $reader;
    }

    /**
     * @param string|array<string, mixed>|CacheItemPoolInterface $cache
     *
     * @throws ContainerExceptionInterface
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     */
    private function getCache(
        ContainerInterface|ServiceLocatorInterface $container,
        string|array|CacheItemPoolInterface $cache,
        string $requestedName
    ): CacheItemPoolInterface {
        if (is_string($cache) && $container->has($cache)) {
            $cache = $container->get($cache);
        }

        if (is_string($cache)) {
            $cache = $this->getCacheConfig($container, $cache, $requestedName);
        }

        if (is_array($cache)) {
            if (empty($cache['class'])) {
                throw new ServiceNotCreatedException(sprintf(
                    'The required \'class\' configuration option is missing for service \'%s\'',
                    $requestedName,
                ));
            }
            $cache = $this->getService($container, $cache['class'], $requestedName);
        }

        if (!$cache instanceof CacheItemPoolInterface) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The cache must be an object of type \'%s\'; \'%s\' provided for service \'%s\'',
                    CacheItemPoolInterface::class,
                    is_object($cache) ? get_class($cache) : gettype($cache),
                    $requestedName,
                ),
            );
        }

        return $cache;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ContainerExceptionInterface
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    private function getCacheConfig(ContainerInterface $container, string $name, string $requestedName): array
    {
        /** @var DoctrineConfigInterface $doctrineConfig */
        $doctrineConfig = $this->getService($container, DoctrineConfigInterface::class, $requestedName);

        if (!$doctrineConfig instanceof DoctrineConfigInterface || !$doctrineConfig->hasCacheConfig($name)) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The cache configuration \'%s\' could not be found for service \'%s\'',
                    $name,
                    $requestedName,
                )
            );
        }

        return $doctrineConfig->getCacheConfig($name);
    }
}
