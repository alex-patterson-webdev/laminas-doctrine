<?php

declare(strict_types=1);

namespace ArpTest\LaminasDoctrine\Service;

use Arp\LaminasDoctrine\Service\ConfigurationFactory;
use Arp\LaminasDoctrine\Service\ConfigurationFactoryInterface;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers  \Arp\LaminasDoctrine\Service\ConfigurationFactory
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package ArpTest\LaminasDoctrine\Service
 */
class ConfigurationFactoryTest extends TestCase
{
    /**
     * @var ServiceManager|MockObject
     */
    private $serviceManager;

    /**
     * Prepare the test case dependencies
     */
    public function setUp(): void
    {
        $this->serviceManager = $this->createMock(ServiceManager::class);
    }

    /**
     * Assert that the ConfigurationFactory implement ConfigurationFactoryInterface
     */
    public function testImplementsConfigurationInterface(): void
    {
        $factory = new ConfigurationFactory($this->serviceManager);

        $this->assertInstanceOf(ConfigurationFactoryInterface::class, $factory);
    }
}
