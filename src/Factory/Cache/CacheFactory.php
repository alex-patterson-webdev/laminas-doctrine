<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Cache;

use Arp\LaminasDoctrine\Config\DoctrineConfigInterface;
use Arp\LaminasFactory\AbstractFactory;
use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class CacheFactory extends AbstractFactory
{
    /**
     * @throws ServiceNotCreatedException
     * @throws ContainerExceptionInterface
     * @throws ServiceNotFoundException
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array $options = null
    ): DoctrineProvider {
        $options = $options ?? $this->getServiceOptions($container, $requestedName, 'cache');

        $name = $options['name'] ?? null;
        if (empty($name)) {
            throw new ServiceNotCreatedException(
                sprintf('The required \'name\' configuration option is missing for service \'%s\'', $requestedName)
            );
        }

        $config = $this->getCacheConfig($container, $name, $requestedName);

        $cache = $config['class'] ?? null;
        if (empty($cache)) {
            throw new ServiceNotCreatedException(
                sprintf('The required \'class\' configuration option is missing for service \'%s\'', $requestedName)
            );
        }

        if (is_string($cache)) {
            $cache = $this->getService($container, $cache, $requestedName);
        }

        if (!$cache instanceof CacheItemPoolInterface) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The cache should be an object of type \'%s\'; \'%s\' provided for service \'%s\'',
                    CacheItemPoolInterface::class,
                    is_object($cache) ? get_class($cache) : gettype($cache),
                    $requestedName,
                ),
            );
        }

        /** @var DoctrineProvider $provider */
        $provider = DoctrineProvider::wrap($cache);

        if (!empty($config['namespace'])) {
            $provider->setNamespace($config['namespace']);
        }

        return $provider;
    }

    /**
     * @return array<string, string>
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ServiceNotCreatedException
     */
    private function getCacheConfig(ContainerInterface $container, string $cacheName, string $requestedName): array
    {
        /** @var DoctrineConfigInterface $doctrineConfig */
        $doctrineConfig = $container->get(DoctrineConfigInterface::class);

        if (!$doctrineConfig->hasCacheConfig($cacheName)) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'Unable to find cache configuration for \'%s\' for service \'%s\'',
                    $cacheName,
                    $requestedName
                )
            );
        }

        return $doctrineConfig->getCacheConfig($cacheName);
    }
}
