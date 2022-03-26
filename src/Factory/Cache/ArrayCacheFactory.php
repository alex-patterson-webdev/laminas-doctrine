<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Cache;

use Arp\LaminasFactory\AbstractFactory;
use Doctrine\Common\Cache\ArrayCache;
use Psr\Container\ContainerInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Cache
 */
final class ArrayCacheFactory extends AbstractFactory
{
    /**
     * @param ContainerInterface        $container
     * @param string                    $requestedName
     * @param array<string, mixed>|null $options
     *
     * @return ArrayCache
     */
    public function __invoke(ContainerInterface $container, string $requestedName, array $options = null): ArrayCache
    {
        return new ArrayCache();
    }
}
