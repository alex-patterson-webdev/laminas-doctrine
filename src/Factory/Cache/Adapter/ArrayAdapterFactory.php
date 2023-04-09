<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Cache\Adapter;

use Arp\LaminasFactory\AbstractFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Exception\InvalidArgumentException;

final class ArrayAdapterFactory extends AbstractFactory
{
    /**
     * @param array<string, mixed>|null $options
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container, string $requestedName, array $options = null): ArrayAdapter
    {
        $options = $options ?? $this->getServiceOptions($container, $requestedName, 'cache');

        try {
            return new ArrayAdapter(
                $options['default_lifetime'] ?? 0,
                $options['store_serialized'] ?? false,
                $options['max_lifetime'] ?? 0,
                $options['max_items'] ?? 0,
            );
        } catch (InvalidArgumentException $e) {
            throw new ServiceNotCreatedException(
                sprintf('Failed to create cache adapter \'%s\' due to invalid configuration options', $requestedName),
                $e->getCode(),
                $e,
            );
        }
    }
}
