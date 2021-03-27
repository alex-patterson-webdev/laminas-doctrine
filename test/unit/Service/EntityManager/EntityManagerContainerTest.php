<?php

declare(strict_types=1);

namespace ArpTest\LaminasDoctrine\Service\EntityManager;

use Arp\LaminasDoctrine\Service\EntityManager\ContainerInterface;
use Arp\LaminasDoctrine\Service\EntityManager\EntityManagerContainer;
use Laminas\ServiceManager\Exception\InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface as PsrContainerInterface;

/**
 * @covers \Arp\LaminasDoctrine\Service\EntityManager\EntityManagerContainer
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package ArpTest\LaminasDoctrine\Service\EntityManager
 */
final class EntityManagerContainerTest extends TestCase
{
    /**
     * @var PsrContainerInterface&MockObject
     */
    private $psrContainer;

    /**
     * Prepare the test case dependencies
     */
    public function setUp(): void
    {
        $this->psrContainer = $this->createMock(PsrContainerInterface::class);
    }

    /**
     * Assert the class implements ContainerInterface
     *
     * @throws InvalidArgumentException
     */
    public function testImplementsContainerInterface(): void
    {
        $container = new EntityManagerContainer($this->psrContainer);

        $this->assertInstanceOf(ContainerInterface::class, $container);
    }
}
