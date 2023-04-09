<?php

declare(strict_types=1);

namespace ArpTest\LaminasDoctrine\Factory\Annotation;

use Arp\LaminasDoctrine\Config\DoctrineConfigInterface;
use Arp\LaminasDoctrine\Factory\Annotation\PsrCacheReaderFactory;
use Arp\LaminasFactory\FactoryInterface;
use Doctrine\Common\Annotations\PsrCachedReader;
use Doctrine\Common\Annotations\Reader;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

/**
 * @covers \Arp\LaminasDoctrine\Factory\Annotation\PsrCacheReaderFactory
 */
final class PsrCacheReaderFactoryTest extends MockeryTestCase
{
    /**
     * @var ContainerInterface&MockInterface
     */
    private ContainerInterface $container;

    public function setUp(): void
    {
        $this->container = \Mockery::mock(ContainerInterface::class);
    }

    public function testIsCallable(): void
    {
        $factory = new PsrCacheReaderFactory();
        $this->assertIsCallable($factory);
    }

    public function testImplementsFactoryInterface(): void
    {
        $factory = new PsrCacheReaderFactory();
        $this->assertInstanceOf(FactoryInterface::class, $factory);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ServiceNotFoundException
     */
    public function testMissingReaderConfigurationThrowsServiceNotCreatedException(): void
    {
        $factory = new PsrCacheReaderFactory();

        $requestedName = 'FooServiceName';

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage(
            sprintf('The required \'reader\' configuration option is missing for service \'%s\'', $requestedName),
        );

        $factory($this->container, $requestedName, []);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ServiceNotFoundException
     */
    public function testMissingCacheConfigurationThrowsServiceNotCreatedException(): void
    {
        $factory = new PsrCacheReaderFactory();

        $requestedName = 'FooServiceName';
        $options = [
            'reader' => Reader::class,
        ];

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage(
            sprintf('The required \'cache\' configuration option is missing for service \'%s\'', $requestedName),
        );

        $factory($this->container, $requestedName, $options);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ServiceNotFoundException
     */
    public function testInvalidReaderServiceThrowsServiceNotCreatedException(): void
    {
        $factory = new PsrCacheReaderFactory();

        $requestedName = 'FooServiceName';
        $options = [
            'reader' => Reader::class,
            'cache' => CacheItemPoolInterface::class,
        ];

        $this->container->shouldReceive('has')
            ->once()
            ->with($options['reader'])
            ->andReturn(true);

        $reader = new \stdClass(); // Invalid reader class

        $this->container->shouldReceive('get')
            ->once()
            ->with($options['reader'])
            ->andReturn($reader);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage(
            sprintf(
                'The reader must be an object of type \'%s\'; \'%s\' provided for service \'%s\'',
                Reader::class,
                \stdClass::class,
                $requestedName,
            ),
        );

        $factory($this->container, $requestedName, $options);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ServiceNotFoundException
     */
    public function testMissingCacheServiceClassThrowsServiceNotCreatedException(): void
    {
        $factory = new PsrCacheReaderFactory();

        $requestedName = 'FooServiceName';
        $options = [
            'reader' => \Mockery::mock(Reader::class),
            'cache' => [],
        ];

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage(
            sprintf('The required \'class\' configuration option is missing for service \'%s\'', $requestedName),
        );

        $factory($this->container, $requestedName, $options);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ServiceNotFoundException
     */
    public function testInvalidCacheServiceThrowsServiceNotCreatedException(): void
    {
        $factory = new PsrCacheReaderFactory();

        $requestedName = 'FooServiceName';

        /** @var Reader&MockInterface $reader */
        $reader = \Mockery::mock(Reader::class);

        $options = [
            'reader' => $reader,
            'cache' => [
                'class' => \stdClass::class,
            ],
        ];

        $cache = new \stdClass();
        $this->container->shouldReceive('get')
            ->once()
            ->with($options['cache']['class'])
            ->andReturn($cache);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage(
            sprintf(
                'The cache must be an object of type \'%s\'; \'%s\' provided for service \'%s\'',
                CacheItemPoolInterface::class,
                \stdClass::class,
                $requestedName,
            ),
        );

        $factory($this->container, $requestedName, $options);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ServiceNotFoundException
     */
    public function testMissingCacheConfigThrowsServiceNotCreatedException(): void
    {
        $factory = new PsrCacheReaderFactory();

        $requestedName = 'FooServiceName';
        $cacheName = 'FooCacheName';
        $options = [
            'reader' => \Mockery::mock(Reader::class),
            'cache' => $cacheName,
        ];

        $this->container->shouldReceive('has')
            ->once()
            ->with($cacheName)
            ->andReturnFalse();

        $this->container->shouldReceive('has')
            ->once()
            ->with(DoctrineConfigInterface::class)
            ->andReturnTrue();

        /** @var DoctrineConfigInterface&MockInterface $doctrineConfig */
        $doctrineConfig = \Mockery::mock(DoctrineConfigInterface::class);

        $this->container->shouldReceive('get')
            ->once()
            ->with(DoctrineConfigInterface::class)
            ->andReturn($doctrineConfig);

        $doctrineConfig->shouldReceive('hasCacheConfig')
            ->once()
            ->with($cacheName)
            ->andReturnFalse();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage(
            sprintf('The cache configuration \'%s\' could not be found for service \'%s\'', $cacheName, $requestedName),
        );

        $factory($this->container, $requestedName, $options);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws \InvalidArgumentException
     * @throws ServiceNotFoundException
     */
    public function testInvoke(): void
    {
        $factory = new PsrCacheReaderFactory();

        $requestedName = 'FooServiceName';

        /** @var Reader&MockInterface $reader */
        $reader = \Mockery::mock(Reader::class);

        /** @var CacheItemPoolInterface&MockInterface $cache */
        $cache = \Mockery::mock(CacheItemPoolInterface::class);

        $options = [
            'reader' => Reader::class,
            'cache' => CacheItemPoolInterface::class,
        ];

        $this->container->shouldReceive('has')
            ->once()
            ->with($options['reader'])
            ->andReturnTrue();

        $this->container->shouldReceive('get')
            ->once()
            ->with($options['reader'])
            ->andReturn($reader);

        $this->container->shouldReceive('has')
            ->once()
            ->with($options['cache'])
            ->andReturnTrue();

        $this->container->shouldReceive('get')
            ->once()
            ->with($options['cache'])
            ->andReturn($cache);

        $this->assertInstanceOf(PsrCachedReader::class, $factory($this->container, $requestedName, $options));
    }
}
