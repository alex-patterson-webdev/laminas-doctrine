<?php

declare(strict_types=1);

namespace ArpTest\LaminasDoctrine\Service\Connection;

use Arp\LaminasDoctrine\Service\Configuration\ConfigurationManagerInterface;
use Arp\LaminasDoctrine\Service\Configuration\Exception\ConfigurationManagerException;
use Arp\LaminasDoctrine\Service\Connection\ConnectionFactory;
use Arp\LaminasDoctrine\Service\Connection\ConnectionFactoryInterface;
use Arp\LaminasDoctrine\Service\Connection\Exception\ConnectionFactoryException;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers  \Arp\LaminasDoctrine\Service\Connection\ConnectionFactory
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package ArpTest\LaminasDoctrine\Service\Connection
 */
final class ConnectionFactoryTest extends TestCase
{
    /**
     * @var ConfigurationManagerInterface|MockObject
     */
    private $configurationManager;

    /**
     * Prepare the test case dependencies
     */
    public function setUp(): void
    {
        $this->configurationManager = $this->createMock(ConfigurationManagerInterface::class);
    }

    /**
     * Assert the class implements ConnectionFactoryInterface
     */
    public function testImplementsConnectionFactoryInterface(): void
    {
        $factory = new ConnectionFactory($this->configurationManager);

        $this->assertInstanceOf(ConnectionFactoryInterface::class, $factory);
    }

    /**
     * Assert that a ConnectionFactoryException is thrown if the provided $configuration is invalid
     *
     * @throws ConnectionFactoryException
     */
    public function testCreateWillThrowAConnectionFactoryExceptionWithInvalidConfiguration(): void
    {
        $factory = new ConnectionFactory($this->configurationManager);

        /** @var EventManager|MockObject $eventManager */
        $eventManager = $this->createMock(EventManager::class);
        $config = [
            'foo' => 'bar',
        ];

        $configuration = 'ConfigurationServiceName'; // Passing string requires the manager to load it

        $exceptionMessage = 'This is a test exception message for ' . __FUNCTION__;
        $exceptionCode = 8910;
        $exception = new ConfigurationManagerException($exceptionMessage, $exceptionCode);

        $this->configurationManager->expects($this->once())
            ->method('getConfiguration')
            ->with($configuration)
            ->willThrowException($exception);

        $this->expectException(ConnectionFactoryException::class);
        $this->expectExceptionCode($exceptionCode);
        $this->expectExceptionMessage(sprintf('Failed to create new connection: %s', $exceptionMessage));

        $factory->create($config, $configuration, $eventManager);
    }

    /**
     * Assert that the create() method will return a configuration instance
     *
     * @param array                                $defaultConfig
     * @param array                                $config
     * @param Configuration|MockObject|string|null $configuration
     * @param EventManager|MockObject|null         $eventManager
     *
     * @throws ConnectionFactoryException
     * @dataProvider getCreateWillReturnConfigurationData
     */
    public function testCreateWillReturnConfiguration(
        array $defaultConfig = [],
        array $config = [],
        $configuration = null,
        ?EventManager $eventManager = null
    ): void {
        $config = array_replace_recursive($defaultConfig, $config);

        if (is_string($configuration)) {
            $configurationString = $configuration;

            /** @var Configuration|MockObject $configuration */
            $configuration = $this->createMock(Configuration::class);
            $this->configurationManager->expects($this->once())
                ->method('getConfiguration')
                ->with($configurationString)
                ->willReturn($configuration);
        }

        /** @var Connection|MockObject $connection */
        $connection = $this->createMock(Connection::class);
        $doCreate = static function (
            array $params,
            ?Configuration $configurationArg,
            ?EventManager $eventManagerArg
        ) use ($connection, $defaultConfig, $config, $configuration, $eventManager): Connection {
            Assert::assertSame($configurationArg, $configuration);
            Assert::assertSame($eventManagerArg, $eventManager);
            Assert::assertSame(
                $params,
                array_replace_recursive($defaultConfig['params'] ?? [], $config['params'] ?? [])
            );
            return $connection;
        };

        $factory = new ConnectionFactory($this->configurationManager, $doCreate);

        $this->assertSame(
            $connection,
            $factory->create($config, $configurationString ?? $configuration, $eventManager)
        );
    }

    /**
     * @return array
     */
    public function getCreateWillReturnConfigurationData(): array
    {
        /** @var EventManager|MockObject $eventManager */
        $eventManager = $this->createMock(EventManager::class);

        /** @var Configuration|MockObject $configuration */
        $configuration = $this->createMock(Configuration::class);

        return [
            // Empty config test
            [

            ],

            // Config & Configuration
            [
                [],
                [
                    'foo'    => 'bar',
                    'params' => [
                        'test' => 123,
                    ],
                ],
                $configuration
            ],

            // Sting configuration
            [
                [],
                [],
                'FooConfigService',
            ],

            // Config & Configuration
            [
                [
                    'params' => [
                        'hello' => 123,
                    ],
                ],
                [
                    'foo'    => 'bar',
                    'params' => [
                        'test' => 123,
                    ],
                ],
                $configuration
            ],

            // Config, Configuration, EventManager
            [
                [
                    'params' => [
                        'test' => 999,
                    ],
                ],
                [
                    'bar'    => 'foo',
                    'params' => [
                        'database' => 'hello',
                        'user' => 'fred',
                    ],
                ],
                $configuration,
                $eventManager
            ],

        ];
    }
}
