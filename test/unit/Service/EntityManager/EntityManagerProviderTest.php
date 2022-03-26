<?php

declare(strict_types=1);

namespace ArpTest\LaminasDoctrine\Service\EntityManager;

use Arp\LaminasDoctrine\Config\EntityManagerConfigs;
use Arp\LaminasDoctrine\Factory\Service\EntityManager\EntityManagerFactory;
use Arp\LaminasDoctrine\Service\ContainerInterface;
use Arp\LaminasDoctrine\Service\EntityManager\EntityManagerProvider;
use Arp\LaminasDoctrine\Service\EntityManager\EntityManagerProviderInterface;
use Arp\LaminasDoctrine\Service\EntityManager\Exception\EntityManagerProviderException;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers  \Arp\LaminasDoctrine\Service\EntityManager\EntityManagerProvider
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package ArpTest\LaminasDoctrine\Service
 */
final class EntityManagerProviderTest extends TestCase
{
    /**
     * @var EntityManagerConfigs&MockObject
     */
    private $config;

    /**
     * @var ContainerInterface&MockObject
     */
    private $container;

    /**
     * Prepare the test case dependencies
     */
    public function setUp(): void
    {
        $this->config = $this->createMock(EntityManagerConfigs::class);

        $this->container = $this->createMock(ContainerInterface::class);
    }

    /**
     * Assert the class is an instance of the EntityManagerProviderInterface
     *
     * @throws EntityManagerProviderException
     */
    public function testImplementsEntityManagerProviderInterface(): void
    {
        $provider = new EntityManagerProvider($this->config, $this->container);

        $this->assertInstanceOf(EntityManagerProviderInterface::class, $provider);
    }

    /**
     * Assert has() will return the expected boolean result
     *
     * @param bool $expected
     * @param bool $hasContainer
     * @param bool $hasConfig
     *
     * @dataProvider getHasData
     *
     * @throws EntityManagerProviderException
     */
    public function testHas(bool $expected, bool $hasContainer, bool $hasConfig): void
    {
        $name = 'FooServiceName';
        if ($hasContainer) {
            $this->container->expects($this->once())
                ->method('has')
                ->with($name)
                ->willReturn(true);
        }

        if (!$hasContainer) {
            $this->config->expects($this->once())
                ->method('hasEntityManagerConfig')
                ->with($name)
                ->willReturn($hasConfig);
        }

        $provider = new EntityManagerProvider($this->config, $this->container);

        $this->assertSame($expected, $provider->hasEntityManager($name));
    }

    /**
     * @return array<mixed>
     */
    public function getHasData(): array
    {
        return [
            [true, true, true],
            [true, true, false],
            [true, false, true],
            [false, false, false],
        ];
    }

    /**
     * Assert that getCollection() will return a matching connection by name
     *
     * @throws EntityManagerProviderException
     */
    public function testGetEntityManagerWillReturnEntityManagerByName(): void
    {
        $provider = new EntityManagerProvider($this->config, $this->container);

        /** @var EntityManagerInterface|MockObject $entityManager */
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $name = 'FooEntityManager';

        $this->container->expects($this->exactly(2))
            ->method('has')
            ->withConsecutive([$name], [$name])
            ->willReturn(true, true);

        $this->container->expects($this->once())
            ->method('get')
            ->with($name)
            ->willReturn($entityManager);

        $this->assertSame($entityManager, $provider->getEntityManager($name));
    }

    /**
     * Assert that a single entity manager can be set and then returned by its $name
     *
     * @throws EntityManagerProviderException
     */
    public function testSetAndGetEntityManager(): void
    {
        $provider = new EntityManagerProvider($this->config, $this->container);

        /** @var EntityManagerInterface&MockObject $entityManager */
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $name = 'FooEntityManager';

        $this->container->expects($this->once())
            ->method('setService')
            ->with($name, $entityManager);

        $provider->setEntityManager($name, $entityManager);

        $this->container->expects($this->exactly(2))
            ->method('has')
            ->withConsecutive([$name], [$name])
            ->willReturnOnConsecutiveCalls(true, true);

        $this->container->expects($this->once())
            ->method('get')
            ->with($name)
            ->willReturn($entityManager);

        $this->assertSame($entityManager, $provider->getEntityManager($name));
    }

    /**
     * Assert that a multiple entity manager can be set and then returned by their names
     *
     * @throws EntityManagerProviderException
     */
    public function testSetAndGetEntityManagers(): void
    {
        /** @var array<mixed> $configs */
        $configs = [
            'foo' => $this->createMock(EntityManagerInterface::class),
            'bar' => $this->createMock(EntityManagerInterface::class),
            'test' => [
                'name' => 'hello',
                'test' => 'data',
                'active' => true,
            ],
        ];

        $provider = new EntityManagerProvider($this->config, $this->container);

        $setArgs = $configsArgs = [];
        foreach ($configs as $name => $config) {
            if (is_array($config)) {
                $configsArgs[] = [$name, $config];
            } else {
                $setArgs[] = [$name, $config];
            }
        }

        $this->container->expects($this->exactly(count($setArgs)))
            ->method('setService')
            ->withConsecutive(...$setArgs);

        $this->config->expects($this->exactly(count($configsArgs)))
            ->method('setEntityManagerConfig')
            ->withConsecutive(...$configsArgs);

        $provider->setEntityManagers($configs);
    }

    /**
     * Assert that getCollection() will return a connection by lazy loading the configuration
     *
     * @throws EntityManagerProviderException
     */
    public function testGetEntityManagerWillReturnLazyLoadedEntityManagerByName(): void
    {
        $provider = new EntityManagerProvider($this->config, $this->container);

        /** @var EntityManagerInterface&MockObject $entityManager */
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $name = 'FooEntityManager';
        $config = [
            'foo' => 'bar',
        ];

        $this->container->expects($this->exactly(3))
            ->method('has')
            ->withConsecutive([$name], [$name], [$name])
            ->willReturn(false, false, true);

        $this->config->expects($this->once())
            ->method('hasEntityManagerConfig')
            ->with($name)
            ->willReturn(true);

        $this->config->expects($this->once())
            ->method('getEntityManagerConfig')
            ->with($name)
            ->willReturn($config);

        $this->container->expects($this->once())
            ->method('setFactory')
            ->with($name, EntityManagerFactory::class);

        $this->container->expects($this->once())
            ->method('build')
            ->with($name, $config)
            ->willReturn($entityManager);

        $this->container->expects($this->once())
            ->method('get')
            ->with($name)
            ->willReturn($entityManager);

        $this->assertSame($entityManager, $provider->getEntityManager($name));
    }

    /**
     * Assert a EntityManagerProviderException is thrown from getEntityManager() if the provided $name can not be
     * resolved to a valid entity manager instance
     *
     * @throws EntityManagerProviderException
     */
    public function testGetEntityManagerWillThrowEntityManagerProviderExceptionIfNotFound(): void
    {
        $provider = new EntityManagerProvider($this->config, $this->container);

        $name = 'EntityManagerTest';

        $this->container->expects($this->exactly(2))
            ->method('has')
            ->withConsecutive([$name], [$name])
            ->willReturnOnConsecutiveCalls(false, false);

        $this->config->expects($this->once())
            ->method('hasEntityManagerConfig')
            ->with($name)
            ->willReturn(false);

        $this->expectException(EntityManagerProviderException::class);
        $this->expectExceptionMessage(sprintf('Unable to find entity manager \'%s\'', $name));

        $provider->refresh($name);
    }

    /**
     * Assert a EntityManagerProviderException is thrown from getEntityManager() if the lazy loaded entity manager
     * cannot be created
     *
     * @throws EntityManagerProviderException
     */
    public function testGetEntityManagerWillThrowEntityManagerProviderExceptionIfNotCreated(): void
    {
        $provider = new EntityManagerProvider($this->config, $this->container);

        $name = 'EntityManagerTest';
        $config = [
            'foo' => 123,
            'bar' => 'Hello World!',
        ];

        $this->container->expects($this->exactly(2))
            ->method('has')
            ->withConsecutive([$name], [$name])
            ->willReturnOnConsecutiveCalls(false, false);

        $this->config->expects($this->once())
            ->method('hasEntityManagerConfig')
            ->with($name)
            ->willReturn(true);

        $this->config->expects($this->once())
            ->method('getEntityManagerConfig')
            ->with($name)
            ->willReturn($config);

        $this->container->expects($this->once())
            ->method('setFactory')
            ->with($name, EntityManagerFactory::class);

        $exceptionMessage = 'This is a test exception message for ' . __FUNCTION__;
        $exceptionCode = 12345;
        $exception = new ServiceNotCreatedException($exceptionMessage, $exceptionCode);

        $this->container->expects($this->once())
            ->method('build')
            ->with($name, $config)
            ->willThrowException($exception);

        $this->expectException(EntityManagerProviderException::class);
        $this->expectExceptionCode($exceptionCode);
        $this->expectExceptionMessage(
            sprintf('Failed to create entity manager \'%s\' from configuration: %s', $name, $exceptionMessage)
        );

        $provider->getEntityManager($name);
    }

    /**
     * Assert a EntityManagerProviderException is thrown from getEntityManager() if the lazy loaded entity manager
     * cannot be returned from the container
     *
     * @throws EntityManagerProviderException
     */
    public function testGetEntityManagerWillThrowEntityManagerProviderExceptionIfNotRetrieved(): void
    {
        $provider = new EntityManagerProvider($this->config, $this->container);

        $name = 'EntityManagerTest';

        $this->container->expects($this->exactly(2))
            ->method('has')
            ->withConsecutive([$name], [$name])
            ->willReturnOnConsecutiveCalls(true, true);

        $exceptionMessage = 'This is a test exception message for ' . __FUNCTION__;
        $exceptionCode = 54321;
        $exception = new ServiceNotCreatedException($exceptionMessage, $exceptionCode);

        $this->container->expects($this->once())
            ->method('get')
            ->with($name)
            ->willThrowException($exception);

        $this->expectException(EntityManagerProviderException::class);
        $this->expectExceptionCode($exceptionCode);
        $this->expectExceptionMessage(
            sprintf('Failed retrieve entity manager \'%s\': %s', $name, $exceptionMessage),
        );

        $provider->getEntityManager($name);
    }

    /**
     * Assert an EntityManager instance can be refreshed by closing the existing connection and creating a new
     * instance based on the configuration
     *
     * @throws EntityManagerProviderException
     */
    public function testRefresh(): void
    {
        $provider = new EntityManagerProvider($this->config, $this->container);

        $name = 'FooEntityManager';

        /** @var EntityManagerInterface&MockObject $entityManager */
        $entityManager = $this->createMock(EntityManagerInterface::class);

        /** @var EntityManagerInterface&MockObject $newInstance */
        $newInstance = $this->createMock(EntityManagerInterface::class);

        $config = [
            'foo' => 123,
            'bar' => 'test',
        ];

        $this->container->expects($this->exactly(4))
            ->method('has')
            ->withConsecutive([$name], [$name], [$name], [$name])
            ->willReturnOnConsecutiveCalls(true, true, true, true);

        $this->container->expects($this->once())
            ->method('get')
            ->with($name)
            ->willReturn($entityManager);

        $entityManager->expects($this->once())
            ->method('isOpen')
            ->willReturn(true);

        $entityManager->expects($this->once())
            ->method('close');

        $this->config->expects($this->once())
            ->method('getEntityManagerConfig')
            ->with($name)
            ->willReturn($config);

        $this->container->expects($this->once())
            ->method('build')
            ->with($name, $config)
            ->willReturn($newInstance);

        $this->assertSame($newInstance, $provider->refresh($name));
    }
}
