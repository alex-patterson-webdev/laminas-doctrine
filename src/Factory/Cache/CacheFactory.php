<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Cache;

use Arp\LaminasFactory\AbstractFactory;
use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

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
        $cache = $options['class'] ?? null;

        if (empty($cache)) {
            throw new ServiceNotCreatedException(
                sprintf('The required \'class\' configuration option is missing for \'%s\'', $requestedName)
            );
        }

        if (is_string($cache)) {
            /** @var CacheItemPoolInterface $cache */
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

        if (!empty($options['namespace'])) {
            $provider->setNamespace($options['namespace']);
        }

        return $provider;
    }
}
