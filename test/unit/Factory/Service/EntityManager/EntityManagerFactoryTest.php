<?php

declare(strict_types=1);

namespace ArpTest\LaminasDoctrine\Factory\Service\EntityManager;

use Arp\LaminasDoctrine\Config\EntityManagerConfigs;
use Arp\LaminasDoctrine\Factory\Service\EntityManager\EntityManagerFactory;
use Arp\LaminasDoctrine\Service\Configuration\ConfigurationManagerInterface;
use Arp\LaminasDoctrine\Service\Configuration\Exception\ConfigurationManagerException;
use Arp\LaminasDoctrine\Service\Connection\ConnectionManagerInterface;
use Arp\LaminasDoctrine\Service\Connection\Exception\ConnectionManagerException;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Mapping\EntityListenerResolver;
use Doctrine\ORM\Repository\RepositoryFactory;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

/**
 * @covers \Arp\LaminasDoctrine\Factory\Service\EntityManager\EntityManagerFactory
 */
final class EntityManagerFactoryTest extends MockeryTestCase
{
    /**
     * @var ContainerInterface&ServiceLocatorInterface&MockInterface
     */
    private ServiceLocatorInterface $container;

    /**
     * @var EntityManagerConfigs&MockInterface
     */
    private EntityManagerConfigs $entityManagerConfigs;

    /**
     * @var ConfigurationManagerInterface&MockInterface
     */
    private ConfigurationManagerInterface $configurationManager;

    /**
     * @var ConnectionManagerInterface&MockInterface
     */
    private ConnectionManagerInterface $connectionManager;

    public function setUp(): void
    {
        $this->container = \Mockery::mock(ServiceLocatorInterface::class);
        $this->entityManagerConfigs = \Mockery::mock(EntityManagerConfigs::class);
        $this->configurationManager = \Mockery::mock(ConfigurationManagerInterface::class);
        $this->connectionManager = \Mockery::mock(ConnectionManagerInterface::class);
    }

    public function testIsInvokable(): void
    {
        $this->assertIsCallable(new EntityManagerFactory());
    }

    /**
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     */
    public function testInvokeWillThrowServiceNotCreatedExceptionIfTheRequiredConfigurationConfigIsMissing(): void
    {
        $factory = new EntityManagerFactory();

        $serviceName = 'doctrine.entitymanager.orm_default';
        $emConfig = [
            'connection' => 'FooConnectionName',
            // missing 'configuration' key
        ];

        $this->container->shouldReceive('get')
            ->once()
            ->with(EntityManagerConfigs::class)
            ->andReturn($this->entityManagerConfigs);

        $this->entityManagerConfigs->shouldReceive('getEntityManagerConfig')
            ->once()
            ->with($serviceName)
            ->andReturn($emConfig);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage(
            sprintf(
                'The required \'configuration\' configuration option is missing for service \'%s\'',
                $serviceName
            )
        );

        $factory($this->container, $serviceName);
    }

    /**
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     */
    public function testInvokeWillThrowServiceNotCreatedExceptionIfTheRequiredConnectionConfigIsMissing(): void
    {
        $factory = new EntityManagerFactory();

        $serviceName = 'doctrine.entitymanager.orm_default';
        $emConfig = [
            'connection' => null,
            'configuration' => 'BarConfigurationName',
        ];

        $this->container->shouldReceive('get')
            ->once()
            ->with(EntityManagerConfigs::class)
            ->andReturn($this->entityManagerConfigs);

        $this->entityManagerConfigs->shouldReceive('getEntityManagerConfig')
            ->once()
            ->with($serviceName)
            ->andReturn($emConfig);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage(
            sprintf(
                'The required \'connection\' configuration option is missing for service \'%s\'',
                $serviceName
            )
        );

        $factory($this->container, $serviceName);
    }

    /**
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     */
    public function testInvokeWillThrowServiceNotCreatedExceptionIfTheConnectionServiceIsNotRegistered(): void
    {
        $factory = new EntityManagerFactory();

        /** @var Configuration&MockInterface $configuration */
        $configuration = \Mockery::mock(Configuration::class);

        $serviceName = 'doctrine.entitymanager.orm_default';
        $connectionName = 'foo';
        $emConfig = [
            'configuration' => $configuration,
            'connection' => $connectionName,
        ];

        $this->container->shouldReceive('get')
            ->once()
            ->with(EntityManagerConfigs::class)
            ->andReturn($this->entityManagerConfigs);

        $this->entityManagerConfigs->shouldReceive('getEntityManagerConfig')
            ->once()
            ->with($serviceName)
            ->andReturn($emConfig);

        $this->container->shouldReceive('has')
            ->once()
            ->with(ConnectionManagerInterface::class)
            ->andReturn(true);

        $this->container->shouldReceive('get')
            ->once()
            ->with(ConnectionManagerInterface::class)
            ->andReturn($this->connectionManager);

        $this->connectionManager->shouldReceive('hasConnection')
            ->once()
            ->with($emConfig['connection'])
            ->andReturn(false);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Failed to load connection \'%s\' for service \'%s\': '
                . 'The connection has not been registered with the connection manager',
                $connectionName,
                $serviceName
            )
        );

        $factory($this->container, $serviceName);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ServiceNotFoundException
     */
    public function testInvokeWillThrowServiceNotCreatedExceptionIfTheConnectionCannotBeCreated(): void
    {
        $factory = new EntityManagerFactory();

        /** @var Configuration&MockInterface $configuration */
        $configuration = \Mockery::mock(Configuration::class);

        $serviceName = 'doctrine.entitymanager.orm_default';
        $connectionName = 'foo';
        $emConfig = [
            'configuration' => $configuration,
            'connection' => $connectionName,
        ];

        $this->container->shouldReceive('get')
            ->once()
            ->with(EntityManagerConfigs::class)
            ->andReturn($this->entityManagerConfigs);

        $this->entityManagerConfigs->shouldReceive('getEntityManagerConfig')
            ->once()
            ->with($serviceName)
            ->andReturn($emConfig);

        $this->container->shouldReceive('has')
            ->once()
            ->with(ConnectionManagerInterface::class)
            ->andReturn(true);

        $this->container->shouldReceive('get')
            ->once()
            ->with(ConnectionManagerInterface::class)
            ->andReturn($this->connectionManager);

        $this->connectionManager->shouldReceive('hasConnection')
            ->once()
            ->with($emConfig['connection'])
            ->andReturn(true);

        $exception = new ConnectionManagerException('This is a test exception message');

        $this->connectionManager->shouldReceive('getConnection')
            ->once()
            ->with($connectionName)
            ->andThrow($exception);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage(
            sprintf('Failed to load connection \'%s\' for service \'%s\'', $connectionName, $serviceName),
        );

        $factory($this->container, $serviceName);
    }

    /**
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     */
    public function testInvokeWillThrowServiceNotCreatedExceptionIfTheConfigurationConfigIsMissing(): void
    {
        $factory = new EntityManagerFactory();

        $serviceName = 'doctrine.entitymanager.orm_default';
        $emConfig = [
            'connection' => \Mockery::mock(Connection::class),
        ];

        $this->container->shouldReceive('get')
            ->once()
            ->with(EntityManagerConfigs::class)
            ->andReturn($this->entityManagerConfigs);

        $this->entityManagerConfigs->shouldReceive('getEntityManagerConfig')
            ->once()
            ->with($serviceName)
            ->andReturn($emConfig);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage(
            sprintf(
                'The required \'configuration\' configuration option is missing for service \'%s\'',
                $serviceName
            )
        );

        $factory($this->container, $serviceName);
    }

    /**
     * Assert that a ServiceNotCreatedException is thrown from __invoke if the provided 'configuration'
     * string is not a valid configuration
     *
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     */
    public function testInvokeWillThrowServiceNotCreatedExceptionIfTheConfigurationServiceIsNotRegistered(): void
    {
        $factory = new EntityManagerFactory();

        $serviceName = 'doctrine.entitymanager.orm_default';
        $configurationName = 'FooConfiguration';
        $emConfig = [
            'connection' => \Mockery::mock(Connection::class),
            'configuration' => $configurationName,
        ];

        $this->container->shouldReceive('get')
            ->once()
            ->with(EntityManagerConfigs::class)
            ->andReturn($this->entityManagerConfigs);

        $this->entityManagerConfigs->shouldReceive('getEntityManagerConfig')
            ->once()
            ->with($serviceName)
            ->andReturn($emConfig);

        $this->container->shouldReceive('has')
            ->once()
            ->with(ConfigurationManagerInterface::class)
            ->andReturn(true);

        $this->container->shouldReceive('get')
            ->once()
            ->with(ConfigurationManagerInterface::class)
            ->andReturn($this->configurationManager);

        $this->configurationManager->shouldReceive('hasConfiguration')
            ->once()
            ->with($configurationName)
            ->andReturn(false);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Failed to load configuration \'%s\' for service \'%s\': '
                . 'The configuration has not been registered with the configuration manager',
                $configurationName,
                $serviceName
            )
        );

        $factory($this->container, $serviceName);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ServiceNotFoundException
     */
    public function testInvokeWillThrowServiceNotCreatedExceptionIfTheConfigurationCannotBeCreated(): void
    {
        $factory = new EntityManagerFactory();

        $serviceName = 'doctrine.entitymanager.orm_default';
        $configurationName = 'FooConfiguration';
        $emConfig = [
            'connection' => \Mockery::mock(Connection::class),
            'configuration' => $configurationName,
        ];

        $this->container->shouldReceive('get')
            ->once()
            ->with(EntityManagerConfigs::class)
            ->andReturn($this->entityManagerConfigs);

        $this->entityManagerConfigs->shouldReceive('getEntityManagerConfig')
            ->once()
            ->with($serviceName)
            ->andReturn($emConfig);

        $this->container->shouldReceive('has')
            ->once()
            ->with(ConfigurationManagerInterface::class)
            ->andReturn(true);

        $this->container->shouldReceive('get')
            ->once()
            ->with(ConfigurationManagerInterface::class)
            ->andReturn($this->configurationManager);

        $this->configurationManager->shouldReceive('hasConfiguration')
            ->once()
            ->with($configurationName)
            ->andReturn(true);

        $exception = new ConfigurationManagerException('This is a test exception message');

        $this->configurationManager->shouldReceive('getConfiguration')
            ->once()
            ->with($configurationName)
            ->andThrow($exception);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage(
            sprintf('Failed to load configuration \'%s\' for service \'%s\'', $configurationName, $serviceName)
        );

        $factory($this->container, $serviceName);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ServiceNotFoundException
     */
    public function testInvokeWillThrowServiceNotCreatedExceptionIfTheEventManagerIsInvalid(): void
    {
        $factory = new EntityManagerFactory();

        $serviceName = 'doctrine.entitymanager.orm_default';

        $eventManagerName = 'FooEventManager';
        $eventManager = new \stdClass();

        $emConfig = [
            'connection' => \Mockery::mock(Connection::class),
            'configuration' => \Mockery::mock(Configuration::class),
            'event_manager' => $eventManagerName,
        ];

        $this->container->shouldReceive('get')
            ->once()
            ->with(EntityManagerConfigs::class)
            ->andReturn($this->entityManagerConfigs);

        $this->entityManagerConfigs->shouldReceive('getEntityManagerConfig')
            ->once()
            ->with($serviceName)
            ->andReturn($emConfig);

        $this->container->shouldReceive('has')
            ->once()
            ->with($eventManagerName)
            ->andReturn(true);

        $this->container->shouldReceive('get')
            ->once()
            ->with($eventManagerName)
            ->andReturn($eventManager);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage(
            sprintf(
                'The event manager must be an object of type \'%s\'; \'%s\' provided for service \'%s\'',
                EventManager::class,
                get_class($eventManager),
                $serviceName,
            ),
        );

        $factory($this->container, $serviceName);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ServiceNotFoundException
     * @throws ServiceNotCreatedException
     * @throws \InvalidArgumentException
     */
    public function testInvokeReturnsEventManager(): void
    {
        $factory = new EntityManagerFactory();

        $serviceName = 'doctrine.entitymanager.orm_default';

        /** @var Configuration&MockInterface $configuration */
        $configuration = \Mockery::mock(Configuration::class);

        /** @var MappingDriver&MockInterface $mappingDriver */
        $mappingDriver = \Mockery::mock(MappingDriver::class);

        $configuration->shouldReceive('getMetadataDriverImpl')
            ->once()
            ->andReturn($mappingDriver);

        $configuration->shouldReceive('getClassMetadataFactoryName')
            ->once()
            ->andReturn(ClassMetadataFactory::class);

        $configuration->shouldReceive('getMetadataCache')
            ->once()
            ->andReturn(\Mockery::mock(CacheItemPoolInterface::class));

        $configuration->shouldReceive('getRepositoryFactory')
            ->once()
            ->andReturn(\Mockery::mock(RepositoryFactory::class));

        $configuration->shouldReceive('getEntityListenerResolver')
            ->once()
            ->andReturn(\Mockery::mock(EntityListenerResolver::class));

        $configuration->shouldReceive('isLazyGhostObjectEnabled')
            ->once()
            ->andReturn(false);

        $configuration->shouldReceive('getProxyDir')->once()->andReturn('/foo/bar');
        $configuration->shouldReceive('getProxyNamespace')->once()->andReturn('Foo');
        $configuration->shouldReceive('getAutoGenerateProxyClasses')->once()->andReturn(false);
        $configuration->shouldReceive('isSecondLevelCacheEnabled')->times(2)->andReturn(false);

        $emConfig = [
            'connection' => \Mockery::mock(Connection::class),
            'configuration' => $configuration,
            'event_manager' => null,
        ];

        $this->container->shouldReceive('get')
            ->once()
            ->with(EntityManagerConfigs::class)
            ->andReturn($this->entityManagerConfigs);

        $this->entityManagerConfigs->shouldReceive('getEntityManagerConfig')
            ->once()
            ->with($serviceName)
            ->andReturn($emConfig);

        $this->assertInstanceOf(EntityManagerInterface::class, $factory($this->container, $serviceName));
    }
}
