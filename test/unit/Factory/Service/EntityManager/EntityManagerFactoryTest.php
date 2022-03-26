<?php

declare(strict_types=1);

namespace ArpTest\LaminasDoctrine\Factory\Service\EntityManager;

use Arp\LaminasDoctrine\Config\EntityManagerConfigs;
use Arp\LaminasDoctrine\Factory\Service\EntityManager\EntityManagerFactory;
use Arp\LaminasDoctrine\Service\Configuration\ConfigurationManagerInterface;
use Arp\LaminasDoctrine\Service\Configuration\Exception\ConfigurationManagerException;
use Arp\LaminasDoctrine\Service\Connection\ConnectionManagerInterface;
use Arp\LaminasDoctrine\Service\Connection\Exception\ConnectionManagerException;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

/**
 * @covers  \Arp\LaminasDoctrine\Factory\Service\EntityManager\EntityManagerFactory
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package ArpTest\LaminasDoctrine\Factory\Service
 */
final class EntityManagerFactoryTest extends TestCase
{
    /**
     * @var ContainerInterface&MockObject
     */
    private $container;

    /**
     * @var EntityManagerConfigs&MockObject
     */
    private $entityManagerConfigs;

    /**
     * @var ConfigurationManagerInterface&MockObject
     */
    private $configurationManager;

    /**
     * @var ConnectionManagerInterface&MockObject
     */
    private $connectionManager;

    /**
     * Prepare the test case dependencies
     */
    public function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);

        $this->entityManagerConfigs = $this->createMock(EntityManagerConfigs::class);

        $this->configurationManager = $this->createMock(ConfigurationManagerInterface::class);

        $this->connectionManager = $this->createMock(ConnectionManagerInterface::class);
    }

    /**
     * Assert that factory is a callable instance
     */
    public function testIsInvokable(): void
    {
        $factory = new EntityManagerFactory();

        $this->assertIsCallable($factory);
    }

    /**
     * Assert a ServiceNotCreateException is thrown from __invoke() if the required 'configuration' configuration
     * option is missing or null
     *
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

        $this->container->expects($this->once())
            ->method('get')
            ->with(EntityManagerConfigs::class)
            ->willReturn($this->entityManagerConfigs);

        $this->entityManagerConfigs->expects($this->once())
            ->method('getEntityManagerConfig')
            ->with($serviceName)
            ->willReturn($emConfig);

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
     * Assert a ServiceNotCreateException is thrown from __invoke() if the required 'connection' configuration
     * option is missing or null
     *
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     */
    public function testInvokeWillThrowServiceNotCreatedExceptionIfTheRequiredConnectionConfigIsMissing(): void
    {
        $factory = new EntityManagerFactory();

        $serviceName = 'doctrine.entitymanager.orm_default';
        $emConfig = [
            'connection'    => null,
            'configuration' => 'BarConfigurationName',
        ];

        $this->container->expects($this->once())
            ->method('get')
            ->with(EntityManagerConfigs::class)
            ->willReturn($this->entityManagerConfigs);

        $this->entityManagerConfigs->expects($this->once())
            ->method('getEntityManagerConfig')
            ->with($serviceName)
            ->willReturn($emConfig);

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
     * Assert a ServiceNotCreateException is thrown from __invoke() if the required 'configuration' object
     * is of an invalid type
     *
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     */
    public function testInvokeWillThrowServiceNotCreatedExceptionIfTheRequiredConfigurationIsInvalid(): void
    {
        $factory = new EntityManagerFactory();

        $configuration = new \stdClass(); // invalid configuration class
        $serviceName = 'doctrine.entitymanager.orm_default';
        $emConfig = [
            'configuration' => $configuration,
            'connection'    => 'BarConnectionName',
        ];

        $this->container->expects($this->once())
            ->method('has')
            ->with(ConfigurationManagerInterface::class)
            ->willReturn(true);

        $this->container->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive([EntityManagerConfigs::class], [ConfigurationManagerInterface::class])
            ->willReturnOnConsecutiveCalls($this->entityManagerConfigs, $this->configurationManager);

        $this->entityManagerConfigs->expects($this->once())
            ->method('getEntityManagerConfig')
            ->with($serviceName)
            ->willReturn($emConfig);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage(
            sprintf(
                'The configuration must be an object of type \'%s\'; \'%s\' provided for service \'%s\'',
                Configuration::class,
                \stdClass::class,
                $serviceName
            )
        );

        $factory($this->container, $serviceName);
    }

    /**
     * Assert a ServiceNotCreateException is thrown from __invoke() if the required 'connection' object
     * is of an invalid type
     *
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     */
    public function testInvokeWillThrowServiceNotCreatedExceptionIfTheRequiredConnectionIsInvalid(): void
    {
        $factory = new EntityManagerFactory();

        $connection = new \stdClass(); // invalid configuration class

        /** @var Configuration|MockObject $configuration */
        $configuration = $this->createMock(Configuration::class);

        $serviceName = 'doctrine.entitymanager.orm_default';
        $emConfig = [
            'configuration' => $configuration,
            'connection'    => $connection,
        ];

        $this->container->expects($this->exactly(2))
            ->method('has')
            ->withConsecutive([ConfigurationManagerInterface::class], [ConnectionManagerInterface::class])
            ->willReturnOnConsecutiveCalls(true, true);

        $this->container->expects($this->exactly(3))
            ->method('get')
            ->withConsecutive(
                [EntityManagerConfigs::class],
                [ConfigurationManagerInterface::class],
                [ConnectionManagerInterface::class]
            )
            ->willReturnOnConsecutiveCalls(
                $this->entityManagerConfigs,
                $this->configurationManager,
                $this->connectionManager
            );

        $this->entityManagerConfigs->expects($this->once())
            ->method('getEntityManagerConfig')
            ->with($serviceName)
            ->willReturn($emConfig);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage(
            sprintf(
                'The connection must be an object of type \'%s\'; \'%s\' provided for service \'%s\'',
                Connection::class,
                \stdClass::class,
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
    public function testInvokeWillThrowServiceNotCreatedExceptionIfTheStringConfigurationCannotBeFound(): void
    {
        $factory = new EntityManagerFactory();

        $configurationName = 'BarConfigurationName';
        $serviceName = 'doctrine.entitymanager.orm_default';
        $emConfig = [
            'configuration' => $configurationName,
            'connection'    => 'FooConnectionName',
        ];

        $this->container->expects($this->once())
            ->method('has')
            ->with(ConfigurationManagerInterface::class)
            ->willReturn(true);

        $this->container->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                [EntityManagerConfigs::class],
                [ConfigurationManagerInterface::class]
            )->willReturnOnConsecutiveCalls(
                $this->entityManagerConfigs,
                $this->configurationManager
            );

        $this->entityManagerConfigs->expects($this->once())
            ->method('getEntityManagerConfig')
            ->with($serviceName)
            ->willReturn($emConfig);

        // Return false for container has() call will raise our exception for missing configuration
        $this->configurationManager->expects($this->once())
            ->method('hasConfiguration')
            ->with($configurationName)
            ->willReturn(false);

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
     * Assert that a ServiceNotCreatedException is thrown from __invoke if the provided 'configuration'
     * string is unable to be created
     *
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     */
    public function testInvokeWillThrowServiceNotCreatedExceptionIfTheStringConfigurationCannotBeCreated(): void
    {
        $factory = new EntityManagerFactory();

        $configurationName = 'BarConfigurationName';
        $serviceName = 'doctrine.entitymanager.orm_default';
        $emConfig = [
            'configuration' => $configurationName,
            'connection'    => 'FooConnectionName',
        ];

        $this->container->expects($this->once())
            ->method('has')
            ->with(ConfigurationManagerInterface::class)
            ->willReturn(true);

        $this->container->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                [EntityManagerConfigs::class],
                [ConfigurationManagerInterface::class]
            )->willReturnOnConsecutiveCalls(
                $this->entityManagerConfigs,
                $this->configurationManager
            );

        $this->entityManagerConfigs->expects($this->once())
            ->method('getEntityManagerConfig')
            ->with($serviceName)
            ->willReturn($emConfig);

        $this->configurationManager->expects($this->once())
            ->method('hasConfiguration')
            ->with($configurationName)
            ->willReturn(true);

        $exceptionMessage = 'This is a test exception message for ' . __METHOD__;
        $exceptionCode = 123;
        $exception = new ConfigurationManagerException($exceptionMessage, $exceptionCode);

        $this->configurationManager->expects($this->once())
            ->method('getConfiguration')
            ->with($configurationName)
            ->willThrowException($exception);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode($exceptionCode);
        $this->expectExceptionMessage(
            sprintf(
                'Failed to load configuration \'%s\' for service \'%s\': %s',
                $configurationName,
                $serviceName,
                $exceptionMessage
            )
        );

        $factory($this->container, $serviceName);
    }

    /**
     * Assert that a ServiceNotCreatedException is thrown from __invoke if the provided 'connection'
     * string is not a valid connection
     *
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     */
    public function testInvokeWillThrowServiceNotCreatedExceptionIfTheStringConnectionCannotBeFound(): void
    {
        $factory = new EntityManagerFactory();

        /** @var Configuration|MockObject $configuration */
        $configuration = $this->createMock(Configuration::class);
        $connectionName = 'FooConnectionName';
        $serviceName = 'doctrine.entitymanager.orm_default';
        $emConfig = [
            'configuration' => $configuration,
            'connection'    => $connectionName,
        ];

        $this->container->expects($this->exactly(2))
            ->method('has')
            ->withConsecutive([ConfigurationManagerInterface::class], [ConnectionManagerInterface::class])
            ->willReturnOnConsecutiveCalls(true, true);

        $this->container->expects($this->exactly(3))
            ->method('get')
            ->withConsecutive(
                [EntityManagerConfigs::class],
                [ConfigurationManagerInterface::class],
                [ConnectionManagerInterface::class]
            )->willReturnOnConsecutiveCalls(
                $this->entityManagerConfigs,
                $this->configurationManager,
                $this->connectionManager
            );

        $this->entityManagerConfigs->expects($this->once())
            ->method('getEntityManagerConfig')
            ->with($serviceName)
            ->willReturn($emConfig);

        // Return false for container has() call will raise our exception for missing configuration
        $this->connectionManager->expects($this->once())
            ->method('hasConnection')
            ->with($connectionName)
            ->willReturn(false);

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
     * Assert that a ServiceNotCreatedException is thrown from __invoke if the provided 'connection'
     * string is unable to be created
     *
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     */
    public function testInvokeWillThrowServiceNotCreatedExceptionIfTheStringConnectionCannotBeCreated(): void
    {
        $factory = new EntityManagerFactory();

        /** @var Configuration|MockObject $configuration */
        $configuration = $this->createMock(Configuration::class);
        $connectionName = 'FooConnectionName';
        $serviceName = 'doctrine.entitymanager.orm_default';
        $emConfig = [
            'configuration' => $configuration,
            'connection'    => $connectionName,
        ];

        $this->container->expects($this->exactly(2))
            ->method('has')
            ->withConsecutive([ConfigurationManagerInterface::class], [ConnectionManagerInterface::class])
            ->willReturnOnConsecutiveCalls(true, true);

        $this->container->expects($this->exactly(3))
            ->method('get')
            ->withConsecutive(
                [EntityManagerConfigs::class],
                [ConfigurationManagerInterface::class],
                [ConnectionManagerInterface::class]
            )->willReturnOnConsecutiveCalls(
                $this->entityManagerConfigs,
                $this->configurationManager,
                $this->connectionManager
            );

        $this->entityManagerConfigs->expects($this->once())
            ->method('getEntityManagerConfig')
            ->with($serviceName)
            ->willReturn($emConfig);

        $this->connectionManager->expects($this->once())
            ->method('hasConnection')
            ->with($connectionName)
            ->willReturn(true);

        $exceptionMessage = 'This is a test exception message for ' . __METHOD__;
        $exceptionCode = 123;
        $exception = new ConnectionManagerException($exceptionMessage, $exceptionCode);

        $this->connectionManager->expects($this->once())
            ->method('getConnection')
            ->with($connectionName)
            ->willThrowException($exception);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionCode($exceptionCode);
        $this->expectExceptionMessage(
            sprintf(
                'Failed to load connection \'%s\' for service \'%s\': %s',
                $connectionName,
                $serviceName,
                $exceptionMessage
            )
        );

        $factory($this->container, $serviceName);
    }
}
