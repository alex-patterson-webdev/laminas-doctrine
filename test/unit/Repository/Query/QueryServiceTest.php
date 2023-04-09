<?php

declare(strict_types=1);

namespace ArpTest\LaminasDoctrine\Repository\Query;

use Arp\Entity\EntityInterface;
use Arp\LaminasDoctrine\Repository\Query\QueryService;
use Arp\LaminasDoctrine\Repository\Query\QueryServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \Arp\LaminasDoctrine\Repository\Query\QueryService
 */
final class QueryServiceTest extends TestCase
{
    /**
     * @var class-string<EntityInterface>
     */
    private string $entityName;

    /**
     * @var EntityManagerInterface&MockObject
     */
    private EntityManagerInterface $entityManager;

    /**
     * @var LoggerInterface&MockObject
     */
    private LoggerInterface $logger;

    public function setUp(): void
    {
        $this->entityName = EntityInterface::class;
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testImplementsQueryServiceInterface(): void
    {
        $queryService = new QueryService($this->entityName, $this->entityManager, $this->logger);

        $this->assertInstanceOf(QueryServiceInterface::class, $queryService);
    }

    public function testGetEntityName(): void
    {
        $queryService = new QueryService($this->entityName, $this->entityManager, $this->logger);

        $this->assertSame($this->entityName, $queryService->getEntityName());
    }
}
