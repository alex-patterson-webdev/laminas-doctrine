<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine;

use Arp\DoctrineEntityRepository\Persistence\PersistService;
use Arp\DoctrineEntityRepository\Query\QueryService;
use Arp\LaminasDoctrineEntityRepository\Repository\RepositoryFactory;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;

return [
    'doctrine' => [
        'connection' => [
            // Add connection parameters in local config
        ],
        'configuration' => [
            'orm_default' => [
                'repository_factory' => RepositoryFactory::class,
                'generate_proxies'   => false,
                'metadata_cache'     => 'array',
                'query_cache'        => 'array',
                'result_cache'       => 'array',
                'hydration_cache'    => 'array',
                'driver'             => 'driver_chain_default',
                'proxy_dir'          => 'data/Doctrine/Proxy',
                'proxy_namespace'    => 'Arp\Proxy',
                'filters'            => [],
                'datetime_functions' => [],
                'string_functions'   => [],
                'numeric_functions'  => [],
                'second_level_cache' => [],
            ],
        ],
        'entitymanager' => [
            'orm_default' => [
                'connection'    => 'orm_default',
                'configuration' => 'orm_default',
            ],
            'persist_default' => [
                'connection'    => 'orm_default',
                'configuration' => 'orm_default',
            ],
        ],
        'driver' => [
            'annotation_driver_default' => [
                'class' => AnnotationDriver::class,
                'cache' => 'array',
                'paths' => [

                ],
            ],
            'driver_chain_default' => [
                'class' => MappingDriverChain::class,
                'drivers' => [

                ],
            ],
        ],
        'cache' => [
            'array' => [
                'class' => ArrayCache::class,
                'namespace' => 'DoctrineModule',
            ],
        ],
    ],
];
