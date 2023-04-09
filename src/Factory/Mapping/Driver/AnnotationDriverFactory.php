<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Mapping\Driver;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\IndexedReader;
use Doctrine\Common\Annotations\PsrCachedReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class AnnotationDriverFactory extends AbstractDriverFactory
{
    /**
     * @param array<string, mixed>|null $options
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, string $requestedName, array $options = null): MappingDriver
    {
        $options = $options ?? $this->getOptions($container, $requestedName, $options);

        $className = $options['class'] ?? null;
        if (empty($className)) {
            throw new ServiceNotCreatedException(
                sprintf('The required \'class\' configuration option is missing for service \'%s\'', $requestedName)
            );
        }

        if (!is_a($className, MappingDriver::class, true)) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The driver configuration option must resolve to a class of type \'%s\'',
                    MappingDriver::class
                )
            );
        }

        $options['reader'] ??= AnnotationReader::class;

        $reader = $this->getReader($container, $options['reader'], $requestedName);

        if (!empty($options['cache']) && $container instanceof ServiceLocatorInterface) {
            $cache = $this->getCache($container, $options['cache'], $requestedName);
            $reader = new PsrCachedReader(new IndexedReader($reader), $cache);
        }

        return new AnnotationDriver($reader, $options['paths'] ?? []);
    }

    /**
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     */
    private function getReader(ContainerInterface $container, string|Reader $reader, string $serviceName): Reader
    {
        if (is_string($reader)) {
            $reader = $this->getService($container, $reader, $serviceName);
        }

        if (!$reader instanceof Reader) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The reader must be an object of type \'%s\'; \'%s\' provided for service \'%s\'',
                    Reader::class,
                    is_object($reader) ? get_class($reader) : gettype($reader),
                    $serviceName
                )
            );
        }

        return $reader;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     */
    private function getCache(
        ServiceLocatorInterface $container,
        string|CacheItemPoolInterface $cache,
        string $serviceName
    ): CacheItemPoolInterface {
        if (is_string($cache)) {
            if ($container->has($cache)) {
                $cache = $container->get($cache);
            } else {
                /** @var DoctrineProvider $provider */
                $provider = $this->buildService($container, Cache::class, ['name' => $cache], $serviceName);

                $cache = $provider->getPool();
            }
        }

        if (!$cache instanceof CacheItemPoolInterface) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The cache must be an object of type \'%s\'; \'%s\' provided for service \'%s\'',
                    CacheItemPoolInterface::class,
                    is_object($cache) ? get_class($cache) : gettype($cache),
                    $serviceName,
                )
            );
        }

        return $cache;
    }
}
