<?php

declare(strict_types=1);

namespace ArpTest\LaminasDoctrine\Service\EntityManager;

use Arp\LaminasDoctrine\Config\DoctrineConfig;
use Arp\LaminasDoctrine\Service\EntityManager\ContainerInterface;
use Arp\LaminasDoctrine\Service\EntityManager\EntityManagerProvider;
use Arp\LaminasDoctrine\Service\EntityManager\EntityManagerProviderInterface;
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
}
