<?php

declare(strict_types=1);

namespace ArpTest\LaminasDoctrine\Service\Configuration;

use Arp\LaminasDoctrine\Config\ConfigurationConfigs;
use Arp\LaminasDoctrine\Service\Configuration\ConfigurationFactoryInterface;
use Arp\LaminasDoctrine\Service\Configuration\ConfigurationManager;
use Arp\LaminasDoctrine\Service\Configuration\ConfigurationManagerInterface;
use Arp\LaminasDoctrine\Service\Configuration\Exception\ConfigurationFactoryException;
use Arp\LaminasDoctrine\Service\Configuration\Exception\ConfigurationManagerException;
use Doctrine\ORM\Configuration;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers  \Arp\LaminasDoctrine\Service\Configuration\ConfigurationManager
 */
final class ConfigurationManagerTest extends TestCase
{
    /**
     * @var ConfigurationFactoryInterface&MockObject
     */
    private ConfigurationFactoryInterface $configurationFactory;

    /**
     * @var ConfigurationConfigs&MockObject
     */
    private ConfigurationConfigs $configs;

    /**
     * Prepare the test case dependencies
     */
    public function setUp(): void
    {
        $this->configurationFactory = $this->createMock(ConfigurationFactoryInterface::class);

        $this->configs = $this->createMock(ConfigurationConfigs::class);
    }

    /**
     * Assert that the configuration manager implements ConfigurationManagerInterface
     */
    public function testImplementsConfigurationManagerInterface(): void
    {
        $manager = new ConfigurationManager($this->configurationFactory, $this->configs);

        $this->assertInstanceOf(ConfigurationManagerInterface::class, $manager);
    }

    /**
     * Assert calls to addConfigurationConfig() will proxy to the internal DoctrineConfig instance
     */
    public function testAddConfigurationConfig(): void
    {
        $manager = new ConfigurationManager($this->configurationFactory, $this->configs);

        $name = 'FooTestConfiguration';
        $config = [
            'foo' => 123,
        ];

        $this->configs->expects($this->once())
            ->method('setConfigurationConfig')
            ->with($name, $config);

        $manager->addConfigurationConfig($name, $config);
    }

    /**
     * Assert that a ConfigurationManagerException is thrown when calling getConfiguration() with a
     * unknown configuration $name
     *
     * @throws ConfigurationManagerException
     */
    public function testGetConfigurationWillThrowConfigurationManagerExceptionForUnknownConnection(): void
    {
        $manager = new ConfigurationManager($this->configurationFactory, $this->configs);

        $name = 'Fred';

        $this->configs->expects($this->once())
            ->method('hasConfigurationConfig')
            ->with($name)
            ->willReturn(false);

        $this->expectException(ConfigurationManagerException::class);
        $this->expectExceptionMessage(
            sprintf('Unable to find Doctrine Configuration registered with name \'%s\'', $name)
        );

        $manager->getConfiguration($name);
    }

    /**
     * Assert a ConfigurationFactoryException is thrown if getConnection() is unable to create a lazy loaded
     * connection instance from matched configuration
     *
     * @throws ConfigurationManagerException
     */
    public function testGetConfigurationWillThrowConfigurationManagerExceptionIfUnableToLazyLoadConfiguration(): void
    {
        $manager = new ConfigurationManager($this->configurationFactory, $this->configs);

        $name = 'Fred';
        $config = [
            'foo' => 123,
        ];

        $this->configs->expects($this->once())
            ->method('hasConfigurationConfig')
            ->with($name)
            ->willReturn(true);

        $this->configs->expects($this->once())
            ->method('getConfigurationConfig')
            ->with($name)
            ->willReturn($config);

        $exceptionCode = 1234;
        $exception = new ConfigurationFactoryException(
            'This is a test exception message for ' . __FUNCTION__,
            $exceptionCode
        );

        $this->configurationFactory->expects($this->once())
            ->method('create')
            ->with($config)
            ->willThrowException($exception);

        $this->expectException(ConfigurationManagerException::class);
        $this->expectExceptionCode($exceptionCode);
        $this->expectExceptionMessage(
            sprintf('Failed to create doctrine configuration \'%s\'', $name),
        );

        $manager->getConfiguration($name);
    }

    /**
     * Assert that getConfiguration() will return a lazy loaded Configuration instance if the provided $name is
     * able to be created
     *
     * @throws ConfigurationManagerException
     */
    public function testGetConfigurationWillLazyLoadAndReturnConfigurationFromDoctrineConfig(): void
    {
        $manager = new ConfigurationManager($this->configurationFactory, $this->configs);

        $name = 'TestConnectionName';

        /** @var Configuration&MockObject $createdConfiguration */
        $createdConfiguration = $this->createMock(Configuration::class);

        $this->configs->expects($this->once())
            ->method('hasConfigurationConfig')
            ->with($name)
            ->willReturn(true);

        $configurationConfig = [
            'foo' => 123,
        ];

        $this->configs->expects($this->once())
            ->method('getConfigurationConfig')
            ->willReturn($configurationConfig);

        $this->configurationFactory->expects($this->once())
            ->method('create')
            ->with($configurationConfig)
            ->willReturn($createdConfiguration);

        $this->assertSame($createdConfiguration, $manager->getConfiguration($name));
    }

    /**
     * Assert that an array of configuration can be set when calling setConfiguration() and retrieved by $name
     * when calling getConfiguration($name)
     *
     * @throws ConfigurationManagerException
     */
    public function testSetAndGetConfigurationObjects(): void
    {
        $manager = new ConfigurationManager($this->configurationFactory, $this->configs);

        $configs = [
            'fred' => $this->createMock(Configuration::class),
            'bob' => $this->createMock(Configuration::class),
            'dick' => $this->createMock(Configuration::class),
            'harry' => $this->createMock(Configuration::class),
        ];

        $manager->setConfigurations($configs);

        foreach ($configs as $name => $config) {
            $this->assertSame($config, $manager->getConfiguration($name));
        }
    }

    /**
     * Assert that setConfigurations() accepts Connection configuration which is added to the internal DoctrineConfig
     */
    public function testSetAndGetConfigurationArray(): void
    {
        $manager = new ConfigurationManager($this->configurationFactory, $this->configs);

        $configs = [
            'fred' => [
                'test' => 123,
            ],
            'bob' => [
                'test' => 456,
            ],
            'jennifer' => [
                'test' => 456,
            ],
        ];

        $setArgs = [];
        foreach ($configs as $name => $config) {
            $setArgs[] = [$name, $config];
        }

        $this->configs->expects($this->exactly(count($setArgs)))
            ->method('setConfigurationConfig')
            ->withConsecutive(...$setArgs);

        $manager->setConfigurations($configs);
    }

    /**
     * Assert has will return $expected bool value if the connection is set in $config or the $doctrineConfig
     *
     * @param bool         $expected
     * @param string       $name
     * @param array<mixed> $configs
     *
     * @dataProvider getHasConfigurationData
     */
    public function testHasConfigurationObject(bool $expected, string $name, array $configs): void
    {
        $manager = new ConfigurationManager($this->configurationFactory, $this->configs);

        if (!empty($configs)) {
            $manager->setConfigurations($configs);
        }

        if (!isset($configs[$name])) {
            $this->configs->expects($this->once())
                ->method('hasConfigurationConfig')
                ->with($name)
                ->willReturn($expected);
        }

        $this->assertSame($expected, $manager->hasConfiguration($name));
    }

    /**
     * @return array<mixed>
     */
    public function getHasConfigurationData(): array
    {
        return [
            // Found match in config
            [
                true,
                'fred',
                [
                    'fred' => $this->createMock(Configuration::class),
                ],
            ],

            // Missing from both
            [
                false,
                'Kitty',
                [
                    'fred' => $this->createMock(Configuration::class),
                ],
            ],

            // In Doctrine Config
            [
                true,
                'Barney',
                [
                    'fred' => $this->createMock(Configuration::class),
                    'bob' => $this->createMock(Configuration::class),
                ],
            ],
        ];
    }
}
