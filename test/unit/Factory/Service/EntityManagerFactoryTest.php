<?php

declare(strict_types=1);

namespace ArpTest\LaminasDoctrine\Factory\Service;

use Arp\LaminasDoctrine\Config\DoctrineConfig;
use Arp\LaminasDoctrine\Factory\Service\EntityManagerFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers \Arp\LaminasDoctrine\Factory\Service\EntityManagerFactory
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package ArpTest\LaminasDoctrine\Factory\Service
 */
final class EntityManagerFactoryTest extends TestCase
{
    /**
     * @var ContainerInterface|MockObject
     */
    private $container;

    /**
     * @var DoctrineConfig
     */
    private $doctrineConfig;

    /**
     * Prepare the test case dependencies
     */
    public function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);

        $this->doctrineConfig = $this->createMock(DoctrineConfig::class);
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
     * Assert a ServiceNotCreateException is thrown from __invoke() if the required 'connection' configuration
     * is missing or null
     */
    public function testInvokeWillThrowServiceNotCreatedExceptionIfTheRequiredConnectionConfigIsMissing(): void
    {
        /** @var EntityManagerFactory|MockObject $factory */
        $factory = new EntityManagerFactory();

        $serviceName = 'doctrine.entitymanager.orm_default';
        $emConfig = [
            'connection' => null,
            'configuration' => 'BarConfigurationName',
        ];

        $this->container->expects($this->once())
            ->method('has')
            ->with(DoctrineConfig::class)
            ->willReturn(true);

        $this->container->expects($this->once())
            ->method('get')
            ->with(DoctrineConfig::class)
            ->willReturn($this->doctrineConfig);

        $this->doctrineConfig->expects($this->once())
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
}
