<?php

declare(strict_types=1);

namespace ArpTest\LaminasDoctrine\Service\EntityManager;

use Arp\LaminasDoctrine\Config\DoctrineConfig;
use Arp\LaminasDoctrine\Factory\Service\EntityManagerFactory;
use Arp\LaminasDoctrine\Service\EntityManager\ContainerInterface;
use Arp\LaminasDoctrine\Service\EntityManager\EntityManagerProvider;
use Arp\LaminasDoctrine\Service\EntityManager\EntityManagerProviderInterface;
use Arp\LaminasDoctrine\Service\EntityManager\Exception\EntityManagerProviderException;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Log\Filter\Mock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers  \Arp\LaminasDoctrine\Service\EntityManager\EntityManagerProvider
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package ArpTest\LaminasDoctrine\Service\EntityManager
 */
final class EntityManagerProviderTest extends TestCase
{
    /**
     * @var DoctrineConfig|MockObject
     */
    private $config;

    /**
     * @var ContainerInterface|MockObject
     */
    private $container;

    /**
     * Prepare the test case dependencies
     */
    public function setUp(): void
    {
        $this->config = $this->createMock(DoctrineConfig::class);

        $this->container = $this->createMock(ContainerInterface::class);
    }

    /**
     * Assert the class is an instance of the EntityManagerProviderInterface
     */
    public function testImplementsEntityManagerProviderInterface(): void
    {
        $provider = new EntityManagerProvider($this->config, $this->container);

        $this->assertInstanceOf(EntityManagerProviderInterface::class, $provider);
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
     * Assert that getCollection() will return a connection by lazy loading the configuration
     *
     * @throws EntityManagerProviderException
     */
    public function testGetEntityManagerWillReturnLazyLoadedEntityManagerByName(): void
    {
        $provider = new EntityManagerProvider($this->config, $this->container);

        /** @var EntityManagerInterface|MockObject $entityManager */
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
     * Assert an EntityManager instance can be refreshed by closing the existing connection and creating a new
     * instance based on the configuration
     *
     * @throws EntityManagerProviderException
     */
    public function testRefresh(): void
    {
        $provider = new EntityManagerProvider($this->config, $this->container);

        $name = 'FooEntityManager';

        /** @var EntityManagerInterface|MockObject $entityManager */
        $entityManager = $this->createMock(EntityManagerInterface::class);

        /** @var EntityManagerInterface|MockObject $newInstance */
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
