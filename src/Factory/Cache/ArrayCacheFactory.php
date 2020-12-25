<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Cache;

use Arp\LaminasFactory\AbstractFactory;
use Doctrine\Common\Cache\ArrayCache;
use Interop\Container\ContainerInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Cache
 */
final class ArrayCacheFactory extends AbstractFactory
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return ArrayCache
     *
     * @noinspection PhpMissingParamTypeInspection
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ArrayCache
    {
        return new ArrayCache();
    }
}
