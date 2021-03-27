<?php

declare(strict_types=1);

namespace ArpTest\LaminasDoctrine\Factory\Service;

use Arp\LaminasDoctrine\Factory\Service\ConfigurationFactoryFactory;
use Arp\LaminasDoctrine\Service\Configuration\ConfigurationFactory;
use Laminas\ServiceManager\ServiceLocatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Arp\LaminasDoctrine\Factory\Service\ConfigurationFactoryFactory
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package ArpTest\LaminasDoctrine\Factory\Service
 */
final class ConfigurationFactoryFactoryTest extends TestCase
{
    /**
     * Assert the factory defines an __invoke() method
     */
    public function testIsCallable(): void
    {
        $this->assertIsCallable(new ConfigurationFactoryFactory());
    }

    /**
     * Assert that the __invoke() method will create a new ConfigurationFactory instance
     */
    public function testInvokeReturnsAConfigurationFactory(): void
    {
        $factory = new ConfigurationFactoryFactory();

        /** @var ServiceLocatorInterface&MockObject $container */
        $container = $this->createMock(ServiceLocatorInterface::class);

        /** @noinspection UnnecessaryAssertionInspection */
        $this->assertInstanceOf(ConfigurationFactory::class, $factory($container, ConfigurationFactory::class));
    }
}
