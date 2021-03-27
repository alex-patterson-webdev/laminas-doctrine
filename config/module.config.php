<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine;

use Arp\DoctrineEntityRepository\Persistence\Event\Listener\CascadeSaveListener;
use Arp\DoctrineEntityRepository\Persistence\Event\Listener\ClearListener;
use Arp\DoctrineEntityRepository\Persistence\Event\Listener\DateCreatedListener;
use Arp\DoctrineEntityRepository\Persistence\Event\Listener\DateDeletedListener;
use Arp\DoctrineEntityRepository\Persistence\Event\Listener\DateTimeListener;
use Arp\DoctrineEntityRepository\Persistence\Event\Listener\DateUpdatedListener;
use Arp\DoctrineEntityRepository\Persistence\Event\Listener\DeleteCollectionListener;
use Arp\DoctrineEntityRepository\Persistence\Event\Listener\EntityValidationListener;
use Arp\DoctrineEntityRepository\Persistence\Event\Listener\ExceptionListener;
use Arp\DoctrineEntityRepository\Persistence\Event\Listener\FlushListener;
use Arp\DoctrineEntityRepository\Persistence\Event\Listener\HardDeleteListener;
use Arp\DoctrineEntityRepository\Persistence\Event\Listener\PersistListener;
use Arp\DoctrineEntityRepository\Persistence\Event\Listener\SaveCollectionListener;
use Arp\DoctrineEntityRepository\Persistence\Event\Listener\SoftDeleteListener;
use Arp\DoctrineEntityRepository\Persistence\Event\Listener\TransactionListener;
use Arp\DoctrineEntityRepository\Persistence\PersistService;
use Arp\DoctrineEntityRepository\Query\QueryService;
use Arp\EventDispatcher\Factory\EventDispatcherFactory;
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
use Arp\LaminasDoctrine\Factory\Repository\Event\Listener\CascadeSaveListenerFactory;
use Arp\LaminasDoctrine\Factory\Repository\Event\Listener\DateCreatedListenerFactory;
use Arp\LaminasDoctrine\Factory\Repository\Event\Listener\DateDeletedListenerFactory;
use Arp\LaminasDoctrine\Factory\Repository\Event\Listener\DateTimeListenerFactory;
use Arp\LaminasDoctrine\Factory\Repository\Event\Listener\DateUpdatedListenerFactory;
use Arp\LaminasDoctrine\Factory\Repository\Event\Listener\EntityListenerProviderFactory;
use Arp\LaminasDoctrine\Factory\Repository\Persistence\PersistServiceFactory;
use Arp\LaminasDoctrine\Factory\Repository\Query\QueryServiceFactory;
use Arp\LaminasDoctrine\Factory\Repository\RepositoryFactoryFactory;
use Arp\LaminasDoctrine\Factory\Repository\RepositoryManagerFactory;
use Arp\LaminasDoctrine\Factory\Service\ConfigurationFactoryFactory;
use Arp\LaminasDoctrine\Factory\Service\ConfigurationManagerFactory;
use Arp\LaminasDoctrine\Factory\Service\ConnectionFactoryFactory;
use Arp\LaminasDoctrine\Factory\Service\ConnectionManagerFactory;
use Arp\LaminasDoctrine\Factory\Service\EntityManagerContainerFactory;
use Arp\LaminasDoctrine\Factory\Service\EntityManagerProviderFactory;
use Arp\LaminasDoctrine\Hydrator\EntityHydrator;
use Arp\LaminasDoctrine\Repository\Event\Listener\EntityListenerProvider;
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
            QueryService::class => [
                'entity_manager' => 'orm_default',
            ],

            PersistService::class => [
                'entity_manager' => 'orm_default',
            ],

            Loader::class => [
                'fixtures' => [

                ],
            ],

            ConnectionManagerInterface::class => [
                'connection_factory' => ConnectionFactoryInterface::class,
            ],
        ],
        'hydrators'     => [
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
            ConnectionFactory::class    => ConnectionFactoryInterface::class,
        ],
        'factories' => [
            // Config
            DoctrineConfig::class                => DoctrineConfigFactory::class,

            // Services
            ConfigurationManagerInterface::class => ConfigurationManagerFactory::class,
            ConfigurationFactoryService::class   => ConfigurationFactoryFactory::class,
            Configuration::class                 => ConfigurationFactory::class,

            ConnectionManagerInterface::class => ConnectionManagerFactory::class,
            ConnectionFactoryInterface::class => ConnectionFactoryFactory::class,
            EntityManagerProvider::class      => EntityManagerProviderFactory::class,
            EntityManagerContainer::class     => EntityManagerContainerFactory::class,
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
            'EntityEventDispatcher'           => EventDispatcherFactory::class,
            EntityListenerProvider::class     => EntityListenerProviderFactory::class,

            EntityValidationListener::class => InvokableFactory::class,
            TransactionListener::class      => InvokableFactory::class,
            ExceptionListener::class        => InvokableFactory::class,
            DateTimeListener::class         => DateTimeListenerFactory::class,
            DateCreatedListener::class      => DateCreatedListenerFactory::class,
            DateUpdatedListener::class      => DateUpdatedListenerFactory::class,
            DateDeletedListener::class      => DateDeletedListenerFactory::class,
            CascadeSaveListener::class      => CascadeSaveListenerFactory::class,
            PersistListener::class          => InvokableFactory::class,
            FlushListener::class            => InvokableFactory::class,
            ClearListener::class            => InvokableFactory::class,
            SoftDeleteListener::class       => InvokableFactory::class,
            HardDeleteListener::class       => InvokableFactory::class,
            SaveCollectionListener::class   => InvokableFactory::class,
            DeleteCollectionListener::class => InvokableFactory::class,

        ],
    ],

    'hydrators' => [
        'factories' => [
            EntityHydrator::class => EntityHydratorFactory::class,
        ],
    ],

    'repository_manager' => [
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
