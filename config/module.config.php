<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine;

use Arp\LaminasDoctrine\Config\ConfigurationConfigs;
use Arp\LaminasDoctrine\Config\ConnectionConfigs;
use Arp\LaminasDoctrine\Config\DoctrineConfig;
use Arp\LaminasDoctrine\Config\DoctrineConfigInterface;
use Arp\LaminasDoctrine\Config\EntityManagerConfigs;
use Arp\LaminasDoctrine\Data\DataFixtureManager;
use Arp\LaminasDoctrine\Factory\Cache\Adapter\ArrayAdapterFactory;
use Arp\LaminasDoctrine\Factory\Cache\CacheFactory;
use Arp\LaminasDoctrine\Factory\Config\ConfigurationConfigsFactory;
use Arp\LaminasDoctrine\Factory\Config\ConnectionConfigsFactory;
use Arp\LaminasDoctrine\Factory\Config\DoctrineConfigFactory;
use Arp\LaminasDoctrine\Factory\Config\EntityManagerConfigsFactory;
use Arp\LaminasDoctrine\Factory\Configuration\ConfigurationFactory;
use Arp\LaminasDoctrine\Factory\DataFixture\DataFixtureManagerFactory;
use Arp\LaminasDoctrine\Factory\DataFixture\LoaderFactory;
use Arp\LaminasDoctrine\Factory\DataFixture\OrmExecutorFactory;
use Arp\LaminasDoctrine\Factory\DataFixture\OrmPurgerFactory;
use Arp\LaminasDoctrine\Factory\Hydrator\EntityHydratorFactory;
use Arp\LaminasDoctrine\Factory\Mapping\Driver\AnnotationDriverFactory;
use Arp\LaminasDoctrine\Factory\Mapping\Driver\MappingDriverChainFactory;
use Arp\LaminasDoctrine\Factory\Repository\Persistence\PersistServiceFactory;
use Arp\LaminasDoctrine\Factory\Repository\Persistence\PersistServiceManagerFactory;
use Arp\LaminasDoctrine\Factory\Repository\Query\QueryServiceFactory;
use Arp\LaminasDoctrine\Factory\Repository\Query\QueryServiceManagerFactory;
use Arp\LaminasDoctrine\Factory\Repository\RepositoryFactoryFactory;
use Arp\LaminasDoctrine\Factory\Repository\RepositoryManagerFactory;
use Arp\LaminasDoctrine\Factory\Service\Configuration\ConfigurationFactoryFactory;
use Arp\LaminasDoctrine\Factory\Service\Configuration\ConfigurationManagerFactory;
use Arp\LaminasDoctrine\Factory\Service\Connection\ConnectionFactoryFactory;
use Arp\LaminasDoctrine\Factory\Service\Connection\ConnectionManagerFactory;
use Arp\LaminasDoctrine\Factory\Service\EntityManager\EntityManagerContainerFactory;
use Arp\LaminasDoctrine\Factory\Service\EntityManager\EntityManagerProviderFactory;
use Arp\LaminasDoctrine\Factory\Validator\IsEntityMatchValidatorFactory;
use Arp\LaminasDoctrine\Factory\Validator\IsEntityNoMatchValidatorFactory;
use Arp\LaminasDoctrine\Hydrator\EntityHydrator;
use Arp\LaminasDoctrine\Repository\Persistence\PersistService;
use Arp\LaminasDoctrine\Repository\Persistence\PersistServiceInterface;
use Arp\LaminasDoctrine\Repository\Persistence\PersistServiceManager;
use Arp\LaminasDoctrine\Repository\Query\QueryService;
use Arp\LaminasDoctrine\Repository\Query\QueryServiceManager;
use Arp\LaminasDoctrine\Repository\RepositoryFactory;
use Arp\LaminasDoctrine\Repository\RepositoryManager;
use Arp\LaminasDoctrine\Service\Configuration\ConfigurationFactory as ConfigurationFactoryService;
use Arp\LaminasDoctrine\Service\Configuration\ConfigurationManager;
use Arp\LaminasDoctrine\Service\Configuration\ConfigurationManagerInterface;
use Arp\LaminasDoctrine\Service\Connection\ConnectionFactory;
use Arp\LaminasDoctrine\Service\Connection\ConnectionFactoryInterface;
use Arp\LaminasDoctrine\Service\Connection\ConnectionManager;
use Arp\LaminasDoctrine\Service\Connection\ConnectionManagerInterface;
use Arp\LaminasDoctrine\Service\EntityManager\EntityManagerContainer;
use Arp\LaminasDoctrine\Service\EntityManager\EntityManagerProvider;
use Arp\LaminasDoctrine\Validator\IsEntityMatchValidator;
use Arp\LaminasDoctrine\Validator\IsEntityNoMatchValidator;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

return [
    'arp' => [
        'services' => [
            Loader::class => [
                'fixtures' => [

                ],
            ],
            ConnectionManagerInterface::class => [
                'connection_factory' => ConnectionFactoryInterface::class,
            ],
            QueryService::class => [
                'entity_manager' => 'orm_default',
            ],
            PersistService::class => [
                'entity_manager' => 'orm_default',
            ],
        ],
        'cache' => [
            ArrayAdapter::class => [
                'store_serialized' => true,
                'default_lifetime' => 0,
                'max_lifetime' => 0,
                'max_items' => 0,
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
        'shared' => [
            'EntityEventDispatcher' => false,
        ],
        'aliases' => [
            DoctrineConfigInterface::class => DoctrineConfig::class,

            MappingDriver::class => MappingDriverChain::class,

            // Configuration
            ConfigurationManager::class => ConfigurationManagerInterface::class,

            // Connection
            ConnectionManager::class => ConnectionManagerInterface::class,
            ConnectionFactory::class => ConnectionFactoryInterface::class,

            PersistServiceInterface::class => PersistService::class,
        ],
        'factories' => [
            // Config
            DoctrineConfig::class => DoctrineConfigFactory::class,

            // Configuration
            ConfigurationConfigs::class => ConfigurationConfigsFactory::class,
            ConfigurationManagerInterface::class => ConfigurationManagerFactory::class,
            ConfigurationFactoryService::class => ConfigurationFactoryFactory::class,
            Configuration::class => ConfigurationFactory::class,

            // Connection
            ConnectionConfigs::class => ConnectionConfigsFactory::class,
            ConnectionManagerInterface::class => ConnectionManagerFactory::class,
            ConnectionFactoryInterface::class => ConnectionFactoryFactory::class,

            // EntityManager
            EntityManagerConfigs::class => EntityManagerConfigsFactory::class,
            EntityManagerProvider::class => EntityManagerProviderFactory::class,
            EntityManagerContainer::class => EntityManagerContainerFactory::class,

            // Repository
            RepositoryManager::class => RepositoryManagerFactory::class,
            RepositoryFactory::class => RepositoryFactoryFactory::class,
            QueryServiceManager::class => QueryServiceManagerFactory::class,
            PersistServiceManager::class => PersistServiceManagerFactory::class,
            QueryService::class => QueryServiceFactory::class,
            PersistService::class => PersistServiceFactory::class,

            // Drivers
            MappingDriverChain::class => MappingDriverChainFactory::class,
            AnnotationDriver::class => AnnotationDriverFactory::class,
            AnnotationReader::class => InvokableFactory::class,

            // Cache
            Cache::class => CacheFactory::class,
            ArrayAdapter::class => ArrayAdapterFactory::class,

            // DataFixtures
            DataFixtureManager::class => DataFixtureManagerFactory::class,
            Loader::class => LoaderFactory::class,
            ORMExecutor::class => OrmExecutorFactory::class,
            ORMPurger::class => OrmPurgerFactory::class,
        ],
    ],

    'hydrators' => [
        'factories' => [
            EntityHydrator::class => EntityHydratorFactory::class,
        ],
    ],

    'validators' => [
        'factories' => [
            IsEntityMatchValidator::class => IsEntityMatchValidatorFactory::class,
            IsEntityNoMatchValidator::class => IsEntityNoMatchValidatorFactory::class,
        ],
    ],

    'repository_manager' => [
        'factories' => [

        ],
    ],

    'query_service_manager' => [
        'factories' => [

        ],
    ],

    'persist_service_manager' => [
        'factories' => [

        ],
    ],

    'entity_manager_container' => [
        'factories' => [

        ],
    ],

    'data_fixture_manager' => [
        'factories' => [

        ],
    ],
];
