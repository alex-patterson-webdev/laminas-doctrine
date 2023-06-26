<?php

declare(strict_types=1);

namespace ArpTest\LaminasDoctrine\Repository\Persistence;

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
     * @var EntityManagerInterface&MockObject
     */
    private EntityManagerInterface $entityManager;

    /**
     * @var LoggerInterface&MockObject
     */
    private LoggerInterface $logger;

    private PersistService $persistService;

    public function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->persistService = new PersistService($this->entityManager, $this->logger);
    }

    public function testImplementsPersistServiceInterface(): void
    {
        $this->assertInstanceOf(PersistServiceInterface::class, $this->persistService);
    }

    public function testFlushWillThrowPersistenceExceptionOnError(): void
    {
        $exceptionMessage = 'This is a test exception message for ' . __FUNCTION__;
        $exceptionCode = 717;

        $exception = new \RuntimeException($exceptionMessage, $exceptionCode);

        $this->entityManager->expects($this->once())
            ->method('flush')
            ->willThrowException($exception);

        $this->logger->expects($this->once())
            ->method('error')
            ->with($exceptionMessage, ['exception' => $exception]);

        $this->expectException(PersistenceException::class);
        $this->expectExceptionMessage('Failed to flush');
        $this->expectExceptionCode($exceptionCode);

        $this->persistService->flush();
    }

    /**
     * @throws PersistenceException
     */
    public function testFlushWillProxyToEntityManagerFlush(): void
    {
        $this->entityManager->expects($this->once())->method('flush');

        $this->persistService->flush();
    }
}
