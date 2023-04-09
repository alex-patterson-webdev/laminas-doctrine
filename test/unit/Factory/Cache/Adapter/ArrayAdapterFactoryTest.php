<?php

declare(strict_types=1);

namespace ArpTest\LaminasDoctrine\Factory\Cache\Adapter;

use Arp\LaminasDoctrine\Factory\Cache\Adapter\ArrayAdapterFactory;
use Arp\LaminasFactory\FactoryInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * @covers \Arp\LaminasDoctrine\Factory\Cache\Adapter\ArrayAdapterFactory
 */
final class ArrayAdapterFactoryTest extends MockeryTestCase
{
    /**
     * @var ContainerInterface&MockInterface
     */
    private ContainerInterface $container;

    public function setUp(): void
    {
        $this->container = \Mockery::mock(ContainerInterface::class);
    }

    public function testIsInvokable(): void
    {
        $factory = new ArrayAdapterFactory();
        $this->assertIsCallable($factory);
    }

    public function testImplementsFactoryInterface(): void
    {
        $factory = new ArrayAdapterFactory();
        $this->assertInstanceOf(FactoryInterface::class, $factory);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ServiceNotFoundException
     */
    public function testServiceNotCreatedExceptionIfProvidedWithInvalidArguments(): void
    {
        $factory = new ArrayAdapterFactory();

        $requestedName = ArrayAdapter::class;
        $options = [
            'max_lifetime' => -1,
        ];

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage(
            sprintf('Failed to create cache adapter \'%s\' due to invalid configuration options', $requestedName),
        );

        $factory($this->container, $requestedName, $options);
    }

    public function testInvokeWithProvidedOptions(): void
    {
        $factory = new ArrayAdapterFactory();

        $requestedName = ArrayAdapter::class;
        $options = [
            'max_lifetime' => 3600,
            'max_items' => 10,
            'store_serialized' => false,
        ];

        $this->assertInstanceOf(ArrayAdapter::class, $factory($this->container, $requestedName, $options));
    }

    public function testInvokeWithServiceOptions(): void
    {
        $factory = new ArrayAdapterFactory();

        $requestedName = ArrayAdapter::class;
        $config = [
            'arp' => [
                'cache' => [
                    $requestedName => [
                        'max_lifetime' => 3600,
                    ],
                ],
            ],
        ];

        $this->container->shouldReceive('has')
            ->once()
            ->with('config')
            ->andReturn(true);

        $this->container->shouldReceive('get')
            ->once()
            ->with('config')
            ->andReturn($config);

        $this->assertInstanceOf(ArrayAdapter::class, $factory($this->container, $requestedName));
    }
}
