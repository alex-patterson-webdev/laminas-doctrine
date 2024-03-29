<?php

declare(strict_types=1);

namespace ArpTest\LaminasDoctrine\Service\Connection;

use Arp\LaminasDoctrine\Config\ConnectionConfigs;
use Arp\LaminasDoctrine\Service\Connection\ConnectionFactoryInterface;
use Arp\LaminasDoctrine\Service\Connection\ConnectionManager;
use Arp\LaminasDoctrine\Service\Connection\ConnectionManagerInterface;
use Arp\LaminasDoctrine\Service\Connection\Exception\ConnectionFactoryException;
use Arp\LaminasDoctrine\Service\Connection\Exception\ConnectionManagerException;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers  \Arp\LaminasDoctrine\Service\Connection\ConnectionManager
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package ArpTest\LaminasDoctrine\Service\Connection
 */
final class ConnectionManagerTest extends TestCase
{
    /**
     * @var ConnectionConfigs&MockObject
     */
    private ConnectionConfigs $configs;

    /**
     * @var ConnectionFactoryInterface&MockObject
     */
    private ConnectionFactoryInterface $connectionFactory;

    /**
     * @var array<Connection&MockObject>
     */
    private array $connections = [];

    /**
     * Prepare the test case dependencies
     */
    public function setUp(): void
    {
        $this->configs = $this->createMock(ConnectionConfigs::class);

        $this->connectionFactory = $this->createMock(ConnectionFactoryInterface::class);
    }

    /**
     * Assert that the class implement ConnectionManagerInterface
     */
    public function testImplementsConnectionManagerInterface(): void
    {
        $manager = new ConnectionManager($this->configs, $this->connectionFactory, $this->connections);

        $this->assertInstanceOf(ConnectionManagerInterface::class, $manager);
    }

    /**
     * Assert that a hasConnection() will return a boolean value for existing/missing connection values
     */
    public function testHasConnectionWillReturnBool(): void
    {
        $manager = new ConnectionManager($this->configs, $this->connectionFactory, $this->connections);

        $connections = [
            'baz'  => $this->createMock(Connection::class),
            'bar'  => $this->createMock(Connection::class),
            'test' => [
                'hello' => 123,
                'fred'  => true,
                'test'  => 'Hello',
            ],
        ];

        $this->configs->expects($this->once())
            ->method('setConnectionConfig')
            ->with('test', $connections['test']);

        $this->configs->expects($this->exactly(2))
            ->method('hasConnectionConfig')
            ->withConsecutive(['test'], ['fred'])
            ->willReturnOnConsecutiveCalls(true, false);

        $manager->setConnections($connections);

        $this->assertTrue($manager->hasConnection('baz'));
        $this->assertTrue($manager->hasConnection('bar'));
        $this->assertTrue($manager->hasConnection('test'));
        $this->assertFalse($manager->hasConnection('fred'));
    }

    /**
     * Assert that a connection can be fetched from the collection by $name
     *
     * @throws ConnectionManagerException
     */
    public function testGetConnectionWillReturnANamedConnection(): void
    {
        $manager = new ConnectionManager($this->configs, $this->connectionFactory, $this->connections);

        $name = 'FooConnection';
        /** @var Connection&MockObject $expected */
        $expected = $this->createMock(Connection::class);

        $connections = [
            'bar' => $this->createMock(Connection::class),
            $name => $expected,
        ];

        $manager->setConnections($connections);

        $this->assertSame($expected, $manager->getConnection($name));
    }

    /**
     * Asset A ConnectionManagerException is thrown should a call to getConnection() be unable to return a
     * connection for a known connection $name
     *
     * @throws ConnectionManagerException
     */
    public function testGetConnectionWillThrowConnectionManagerExceptionIfUnableToCreateNamedConnection(): void
    {
        $manager = new ConnectionManager($this->configs, $this->connectionFactory, $this->connections);

        $name = 'FooConnection';
        $connections = [
            $name => [
                'foo'  => 123,
                'test' => 'abc',
            ],
        ];

        $this->configs->expects($this->once())
            ->method('setConnectionConfig')
            ->with($name, $connections[$name]);

        $this->configs->expects($this->once())
            ->method('hasConnectionConfig')
            ->with($name)
            ->willReturn(true);

        $this->configs->expects($this->once())
            ->method('getConnectionConfig')
            ->with($name)
            ->willReturn($connections[$name]);

        $exceptionMessage = 'This is a test exception message for ' . __FUNCTION__;
        $exceptionCode = 876;
        $exception = new ConnectionFactoryException($exceptionMessage, $exceptionCode);

        $this->connectionFactory->expects($this->once())
            ->method('create')
            ->with($connections[$name])
            ->willThrowException($exception);

        $this->expectException(ConnectionManagerException::class);
        $this->expectExceptionCode($exceptionCode);
        $this->expectExceptionMessage(sprintf('Failed to establish connection \'%s\': %s', $name, $exceptionMessage));

        $manager->setConnections($connections);

        $manager->getConnection($name);
    }

    /**
     * Assert that a collection can be lazy loaded from the collection by its $name
     *
     * @throws ConnectionManagerException
     */
    public function testGetConnectionWillLazyLoadAndReturnANamedConnection(): void
    {
        $manager = new ConnectionManager($this->configs, $this->connectionFactory, $this->connections);

        $name = 'FooConnection';
        $connections = [
            $name => [
                'foo'  => 123,
                'test' => 'abc',
            ],
            'foo' => $this->createMock(Connection::class),
        ];

        $this->configs->expects($this->once())
            ->method('setConnectionConfig')
            ->with($name, $connections[$name]);

        $this->configs->expects($this->once())
            ->method('hasConnectionConfig')
            ->with($name)
            ->willReturn(true);

        $this->configs->expects($this->once())
            ->method('getConnectionConfig')
            ->with($name)
            ->willReturn($connections[$name]);

        /** @var Connection&MockObject $expected */
        $expected = $this->createMock(Connection::class);

        $this->connectionFactory->expects($this->once())
            ->method('create')
            ->with($connections[$name])
            ->willReturn($expected);

        $manager->setConnections($connections);

        $this->assertSame($expected, $manager->getConnection($name));
    }

    /**
     * Assert that calling getConnection() with a non-existing connection $name a ConnectionManagerException is thrown
     *
     * @throws ConnectionManagerException
     */
    public function testGetConnectionWillThrowConnectionManagerExceptionIfNotFound(): void
    {
        $manager = new ConnectionManager($this->configs, $this->connectionFactory, $this->connections);

        $name = 'NonExistingName';

        $this->configs->expects($this->once())
            ->method('hasConnectionConfig')
            ->with($name)
            ->willReturn(false);

        $this->expectException(ConnectionManagerException::class);
        $this->expectExceptionMessage(
            sprintf('Failed to establish connection \'%s\': Failed to find a the required configuration', $name)
        );

        $manager->getConnection($name);
    }
}
