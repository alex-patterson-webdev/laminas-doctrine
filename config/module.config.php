<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine;

use Arp\DoctrineEntityRepository\Persistence\PersistService;
use Arp\DoctrineEntityRepository\Query\QueryService;
use Arp\LaminasDoctrine\Config\DoctrineConfig;
use Arp\LaminasDoctrine\Data\DataFixtureManager;
use Arp\LaminasDoctrine\Factory\Cache\ArrayCacheFactory;
use Arp\LaminasDoctrine\Factory\Config\DoctrineConfigFactory;
use Arp\LaminasDoctrine\Factory\Configuration\ConfigurationFactory;
use Arp\LaminasDoctrine\Factory\DataFixture\DataFixtureManagerFactory;
use Arp\LaminasDoctrine\Factory\DataFixture\LoaderFactory;
use Arp\LaminasDoctrine\Factory\DataFixture\OrmExecutorFactory;
use Arp\LaminasDoctrine\Factory\DataFixture\OrmPurgerFactory;
use Arp\LaminasDoctrine\Factory\Hydrator\EntityHydratorFactory;
use Arp\LaminasDoctrine\Factory\Mapping\Driver\AnnotationDriverFactory;
use Arp\LaminasDoctrine\Factory\Mapping\Driver\MappingDriverChainFactory;
use Arp\LaminasDoctrine\Factory\Repository\Event\Listener\EntityListenerProviderFactory;
use Arp\LaminasDoctrine\Factory\Repository\RepositoryFactoryFactory;
use Arp\LaminasDoctrine\Factory\Repository\RepositoryManagerFactory;
use Arp\LaminasDoctrine\Factory\Service\ConfigurationFactoryFactory;
use Arp\LaminasDoctrine\Factory\Service\ConfigurationManagerFactory;
use Arp\LaminasDoctrine\Factory\Service\ConnectionFactoryFactory;
use Arp\LaminasDoctrine\Factory\Service\ConnectionManagerFactory;
use Arp\LaminasDoctrine\Factory\Service\EntityManagerManagerFactory;
use Arp\LaminasDoctrine\Factory\Service\EntityManagerProviderFactory;
use Arp\LaminasDoctrine\Factory\Service\PersistServiceFactory;
use Arp\LaminasDoctrine\Factory\Service\QueryServiceFactory;
use Arp\LaminasDoctrine\Hydrator\EntityHydrator;
use Arp\LaminasDoctrine\Repository\Event\Listener\EntityListenerProvider;
use Arp\LaminasDoctrine\Repository\RepositoryFactory;
use Arp\LaminasDoctrine\Repository\RepositoryManager;
use Arp\LaminasDoctrine\Service\ConfigurationFactory as ConfigurationFactoryService;
use Arp\LaminasDoctrine\Service\ConfigurationManager;
use Arp\LaminasDoctrine\Service\ConfigurationManagerInterface;
use Arp\LaminasDoctrine\Service\ConnectionFactory;
use Arp\LaminasDoctrine\Service\ConnectionManager;
use Arp\LaminasDoctrine\Service\ConnectionManagerInterface;
use Arp\LaminasDoctrine\Service\EntityManagerManager;
use Arp\LaminasDoctrine\Service\EntityManagerProvider;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'arp' => [
        'services'      => [
            QueryService::class   => [
                'entity_manager' => 'orm_default',
            ],
            PersistService::class => [
                'entity_manager' => 'orm_default',
            ],

            Loader::class => [
                'fixtures' => [

                ],
            ],
        ],
        'hydrators' => [
            EntityHydrator::class => [
                'entity_manager' => 'orm_default',
            ],
        ],
        'query_filters' => [

        ],
    ],

    'service_manager' => [
        'aliases'   => [
            MappingDriver::class => MappingDriverChain::class,
            Cache::class         => ArrayCache::class,

            ConfigurationManager::class => ConfigurationManagerInterface::class,
            ConnectionManager::class    => ConnectionManagerInterface::class,
        ],
        'factories' => [
            // Config
            DoctrineConfig::class                => DoctrineConfigFactory::class,

            // Services
            ConfigurationManagerInterface::class => ConfigurationManagerFactory::class,
            ConfigurationFactoryService::class   => ConfigurationFactoryFactory::class,
            Configuration::class                 => ConfigurationFactory::class,

            ConnectionManagerInterface::class => ConnectionManagerFactory::class,
            ConnectionFactory::class          => ConnectionFactoryFactory::class,
            EntityManagerProvider::class      => EntityManagerProviderFactory::class,
            EntityManagerManager::class       => EntityManagerManagerFactory::class,
            RepositoryManager::class          => RepositoryManagerFactory::class,
            RepositoryFactory::class          => RepositoryFactoryFactory::class,
            QueryService::class               => QueryServiceFactory::class,
            PersistService::class             => PersistServiceFactory::class,

            // Drivers
            MappingDriverChain::class         => MappingDriverChainFactory::class,
            AnnotationDriver::class           => AnnotationDriverFactory::class,
            AnnotationReader::class           => InvokableFactory::class,

            // Cache
            ArrayCache::class                 => ArrayCacheFactory::class,

            // DataFixtures
            DataFixtureManager::class         => DataFixtureManagerFactory::class,
            Loader::class                     => LoaderFactory::class,
            ORMExecutor::class                => OrmExecutorFactory::class,
            ORMPurger::class                  => OrmPurgerFactory::class,

            // Repository Event Listeners
            EntityListenerProvider::class     => EntityListenerProviderFactory::class,
        ],
    ],

    'hydrators' => [
        'factories' => [
            EntityHydrator::class => EntityHydratorFactory::class,
        ],
    ],

    'repository_manager'     => [
        'factories' => [

        ],
    ],
    'entity_manager_manager' => [
        'factories' => [

        ],
    ],

    'data_fixture_manager' => [
        'factories' => [

        ],
    ],
];
