<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Service\Query;

use Arp\LaminasDoctrine\Service\Query\EntityFilterService;
use Arp\LaminasFactory\AbstractFactory;
use Interop\Container\ContainerInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Service\Query
 */
final class QueryFilterServiceFactory extends AbstractFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $queryService = $this->buildService($container, EntityFilterService::class, $options, $requestedName);

        return new EntityFilterService($queryService, $queryFilterManager);
    }
}
