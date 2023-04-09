<?php

declare(strict_types=1);

namespace ArpTest\LaminasDoctrine\Repository;

use Arp\LaminasDoctrine\Repository\EntityRepository;
use Arp\Entity\EntityInterface;
use Arp\LaminasDoctrine\Repository\EntityRepositoryInterface;
use Arp\LaminasDoctrine\Repository\Exception\EntityRepositoryException;
use Arp\LaminasDoctrine\Repository\Persistence\PersistServiceInterface;
use Arp\LaminasDoctrine\Repository\Persistence\TransactionServiceInterface;
use Arp\LaminasDoctrine\Repository\Query\Exception\QueryServiceException;
use Arp\LaminasDoctrine\Repository\Query\QueryServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \Arp\LaminasDoctrine\Repository\EntityRepository
 */
final class EntityRepositoryTest extends TestCase
{
    /**
     * @var class-string<EntityInterface>
     */
    private string $entityName;

    /**
     * @var QueryServiceInterface<EntityInterface>&MockObject
     */
    private QueryServiceInterface $queryService;

    /**
     * @var PersistServiceInterface<EntityInterface>&MockObject
     */
    private PersistServiceInterface $persistService;

    /**
     * @var LoggerInterface&MockObject
     */
    private LoggerInterface $logger;

    public function setUp(): void
    {
        $this->entityName = EntityInterface::class;
        $this->queryService = $this->createMock(QueryServiceInterface::class);
        $this->persistService = $this->createMock(PersistServiceInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testImplementsEntityRepositoryInterface(): void
    {
        $repository = new EntityRepository(
            $this->entityName,
            $this->queryService,
            $this->persistService,
            $this->logger
        );

        $this->assertInstanceOf(EntityRepositoryInterface::class, $repository);
    }

    public function testImplementsTransactionServiceInterface(): void
    {
        $repository = new EntityRepository(
            $this->entityName,
            $this->queryService,
            $this->persistService,
            $this->logger
        );

        $this->assertInstanceOf(TransactionServiceInterface::class, $repository);
    }

    public function testGetClassName(): void
    {
        $repository = new EntityRepository(
            $this->entityName,
            $this->queryService,
            $this->persistService,
            $this->logger
        );

        $this->assertSame($this->entityName, $repository->getClassName());
    }

    /**
     * @throws EntityRepositoryException
     */
    public function testFindWillThrowEntityRepositoryExceptionIfUnableToPerformQuery(): void
    {
        $repository = new EntityRepository(
            $this->entityName,
            $this->queryService,
            $this->persistService,
            $this->logger
        );

        $id = 123;

        $exception = new QueryServiceException('This is a test message', 999);

        $this->queryService->expects($this->once())
            ->method('findOneById')
            ->with($id)
            ->willThrowException($exception);

        $errorMessage = sprintf('Unable to find entity of type \'%s\'', $this->entityName);

        $this->logger->expects($this->once())
            ->method('error')
            ->with($errorMessage, ['exception' => $exception, 'id' => $id]);

        $this->expectException(EntityRepositoryException::class);
        $this->expectExceptionMessage($errorMessage);
        $this->expectExceptionCode(999);

        $repository->find($id);
    }

    /**
     * @throws EntityRepositoryException
     */
    public function testFindWillReturnMatchedEntityById(): void
    {
        $repository = new EntityRepository(
            $this->entityName,
            $this->queryService,
            $this->persistService,
            $this->logger
        );

        $id = 123;

        /** @var EntityInterface&MockObject $entity */
        $entity = $this->createMock(EntityInterface::class);

        $this->queryService->expects($this->once())
            ->method('findOneById')
            ->with($id)
            ->willReturn($entity);

        $this->assertSame($entity, $repository->find($id));
    }

    /**
     * @throws EntityRepositoryException
     */
    public function testFindOneByIdWillThrowEntityRepositoryExceptionIfUnableToPerformQuery(): void
    {
        $repository = new EntityRepository(
            $this->entityName,
            $this->queryService,
            $this->persistService,
            $this->logger
        );

        $id = 456;

        $exception = new QueryServiceException('This is a test message', 111);

        $this->queryService->expects($this->once())
            ->method('findOneById')
            ->with($id)
            ->willThrowException($exception);

        $errorMessage = sprintf('Unable to find entity of type \'%s\'', $this->entityName);

        $this->logger->expects($this->once())
            ->method('error')
            ->with($errorMessage, ['exception' => $exception, 'id' => $id]);

        $this->expectException(EntityRepositoryException::class);
        $this->expectExceptionMessage($errorMessage);
        $this->expectExceptionCode(111);

        $repository->findOneById($id);
    }

    /**
     * @throws EntityRepositoryException
     */
    public function testFindOneByIdWillReturnMatchedEntityById(): void
    {
        $repository = new EntityRepository(
            $this->entityName,
            $this->queryService,
            $this->persistService,
            $this->logger
        );

        $id = 456;

        /** @var EntityInterface&MockObject $entity */
        $entity = $this->createMock(EntityInterface::class);

        $this->queryService->expects($this->once())
            ->method('findOneById')
            ->with($id)
            ->willReturn($entity);

        $this->assertSame($entity, $repository->findOneById($id));
    }

    /**
     * @throws EntityRepositoryException
     */
    public function testFindOneByWillThrowEntityRepositoryExceptionIfUnableToPerformQuery(): void
    {
        $repository = new EntityRepository(
            $this->entityName,
            $this->queryService,
            $this->persistService,
            $this->logger
        );

        $criteria = ['foo' => 'bar'];

        $exception = new QueryServiceException('This is a test message for ' . __FUNCTION__, 777);

        $this->queryService->expects($this->once())
            ->method('findOne')
            ->with($criteria)
            ->willThrowException($exception);

        $errorMessage = sprintf('Unable to find entity of type \'%s\'', $this->entityName);

        $this->logger->expects($this->once())
            ->method('error')
            ->with($errorMessage, ['exception' => $exception, 'criteria' => $criteria]);

        $this->expectException(EntityRepositoryException::class);
        $this->expectExceptionMessage($errorMessage);
        $this->expectExceptionCode(777);

        $repository->findOneBy($criteria);
    }

    /**
     * @throws EntityRepositoryException
     */
    public function testFindOneByWillReturnMatchedEntityByCriteria(): void
    {
        $repository = new EntityRepository(
            $this->entityName,
            $this->queryService,
            $this->persistService,
            $this->logger
        );

        $criteria = [
            'test' => 'hello',
            'a' => 'z',
            'foo' => 123,
        ];

        /** @var EntityInterface&MockObject $entity */
        $entity = $this->createMock(EntityInterface::class);

        $this->queryService->expects($this->once())
            ->method('findOne')
            ->with($criteria)
            ->willReturn($entity);

        $this->assertSame($entity, $repository->findOneBy($criteria));
    }
}
