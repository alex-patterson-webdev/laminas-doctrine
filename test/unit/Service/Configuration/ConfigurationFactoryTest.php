<?php

declare(strict_types=1);

namespace ArpTest\LaminasDoctrine\Service\Configuration;

use Arp\LaminasDoctrine\Service\Configuration\ConfigurationFactory;
use Arp\LaminasDoctrine\Service\Configuration\ConfigurationFactoryInterface;
use Arp\LaminasDoctrine\Service\Configuration\Exception\ConfigurationFactoryException;
use Doctrine\ORM\Configuration;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers  \Arp\LaminasDoctrine\Service\Configuration\ConfigurationFactory
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package ArpTest\LaminasDoctrine\Service\Configuration
 */
final class ConfigurationFactoryTest extends TestCase
{
    /**
     * @var ServiceLocatorInterface&MockObject
     */
    private $serviceManager;

    /**
     * Prepare the test case dependencies
     */
    public function setUp(): void
    {
        $this->serviceManager = $this->createMock(ServiceLocatorInterface::class);
    }

    /**
     * Assert that the ConfigurationFactory implement ConfigurationFactoryInterface
     */
    public function testImplementsConfigurationInterface(): void
    {
        $factory = new ConfigurationFactory($this->serviceManager);

        $this->assertInstanceOf(ConfigurationFactoryInterface::class, $factory);
    }

    /**
     * Assert that a ConfigurationFactoryException is thrown when create() is unable to create an instance
     *
     * @throws ConfigurationFactoryException
     */
    public function testCreateWillThrowConfigurationFactoryExceptionIfUnableToCreate(): void
    {
        $config = [
            'foo' => 123,
            'bar' => true,
            'test' => 'Hello'
        ];

        $factory = new ConfigurationFactory($this->serviceManager);

        $exceptionMessage = 'This is a test exception message for ' . __FUNCTION__;
        $exceptionCode = 999;
        $exception = new ServiceNotCreatedException($exceptionMessage, $exceptionCode);

        $this->serviceManager->expects($this->once())
            ->method('build')
            ->with(Configuration::class, $config)
            ->willThrowException($exception);

        $this->expectException(ConfigurationFactoryException::class);
        $this->expectExceptionCode($exceptionCode);
        $this->expectExceptionMessage(
            sprintf('Failed to create ORM Configuration: %s', $exceptionMessage),
        );

        $factory->create($config);
    }

    /**
     * Assert create() will return a ORM Configuration based on the provided $config
     *
     * @throws ConfigurationFactoryException
     */
    public function testCreateReturnAConfiguredOrmConfigurationInstance(): void
    {
        $config = [
            'foo' => 123,
            'bar' => true,
            'test' => 'Hello'
        ];

        $factory = new ConfigurationFactory($this->serviceManager);

        /** @var Configuration&MockObject $configuration */
        $configuration = $this->createMock(Configuration::class);

        $this->serviceManager->expects($this->once())
            ->method('build')
            ->with(Configuration::class, $config)
            ->willReturn($configuration);

        $this->assertSame($configuration, $factory->create($config));
    }
}
