<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine;

use Arp\LaminasDoctrine\Repository\RepositoryFactory;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

return [
    'doctrine' => [
        'connection' => [
            // Add connection parameters in local config
        ],
        'configuration' => [
            'orm_default' => [
                'repository_factory' => RepositoryFactory::class,
                'generate_proxies'   => false,
                'metadata_cache'     => 'array_metadata_cache',
                'query_cache'        => 'array_query_cache',
                'result_cache'       => 'array_result_cache',
                'hydration_cache'    => 'array_hydration_cache',
                'driver'             => 'driver_chain_default',
                'proxy_dir'          => 'data/Doctrine/Proxy',
                'proxy_namespace'    => 'Arp\\Proxy',
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
                'cache' => 'array_annotation_driver_cache',
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
            'array_metadata_cache' => [
                'class' => ArrayAdapter::class,
                'namespace' => 'metadata_cache'
            ],
            'array_query_cache' => [
                'class' => ArrayAdapter::class,
                'namespace' => 'query_cache'
            ],
            'array_result_cache' => [
                'class' => ArrayAdapter::class,
                'namespace' => 'result_cache'
            ],
            'array_hydration_cache' => [
                'class' => ArrayAdapter::class,
                'namespace' => 'hydration_cache'
            ],
            'array_annotation_driver_cache' => [
                'class' => ArrayAdapter::class,
                'namespace' => 'annotation_cache',
            ],
        ],
    ],
];
