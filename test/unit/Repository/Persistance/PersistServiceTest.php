<?php

declare(strict_types=1);

namespace ArpTest\LaminasDoctrine\Repository\Persistance;

use Arp\Entity\EntityInterface;
use Arp\LaminasDoctrine\Repository\Persistence\Exception\PersistenceException;
use Arp\LaminasDoctrine\Repository\Persistence\PersistService;
use Arp\LaminasDoctrine\Repository\Persistence\PersistServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \Arp\LaminasDoctrine\Repository\Persistence\PersistService
 */
final class PersistServiceTest extends TestCase
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

    public function testImplementsPersistServiceInterface(): void
    {
        $persistService = new PersistService($this->entityName, $this->entityManager, $this->logger);

        $this->assertInstanceOf(PersistServiceInterface::class, $persistService);
    }

    public function testGetEntityName(): void
    {
        $persistService = new PersistService($this->entityName, $this->entityManager, $this->logger);

        $this->assertSame($this->entityName, $persistService->getEntityName());
    }

    public function testFlushWillThrowPersistenceExceptionOnError(): void
    {
        $persistService = new PersistService($this->entityName, $this->entityManager, $this->logger);

        $exceptionMessage = 'This is a test exception message for ' . __FUNCTION__;
        $exceptionCode = 717;

        $exception = new \RuntimeException($exceptionMessage, $exceptionCode);

        $this->entityManager->expects($this->once())
            ->method('flush')
            ->willThrowException($exception);

        $this->logger->expects($this->once())
            ->method('error')
            ->with($exceptionMessage, ['exception' => $exception, 'entity_name' => $this->entityName]);

        $this->expectException(PersistenceException::class);
        $this->expectExceptionMessage(sprintf('Failed to flush entity of type \'%s\'', $this->entityName));
        $this->expectExceptionCode($exceptionCode);

        $persistService->flush();
    }

    /**
     * @throws PersistenceException
     */
    public function testFlushWillProxyToEntityManagerFlush(): void
    {
        $persistService = new PersistService($this->entityName, $this->entityManager, $this->logger);

        $this->entityManager->expects($this->once())->method('flush');

        $persistService->flush();
    }
}
