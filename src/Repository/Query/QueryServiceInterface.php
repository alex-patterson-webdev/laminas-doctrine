<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Repository\Query;

use Arp\Entity\EntityInterface;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;

/**
 * @template TEntity of EntityInterface
 */
interface QueryServiceInterface
{
    /**
     * @return class-string<TEntity>
     */
    public function getEntityName(): string;

    /**
     * @param array<string, mixed> $options
     *
     * @return TEntity|null
     *
     * @throws Exception\QueryServiceException
     */
    public function findOneById(int $id, array $options = []): ?EntityInterface;

    /**
     * @param array<string, mixed> $criteria
     * @param array<string, mixed> $options
     *
     * @return TEntity|null
     *
     * @throws Exception\QueryServiceException
     */
    public function findOne(array $criteria, array $options = []): ?EntityInterface;

    /**
     * @param array<string, mixed> $criteria
     * @param array<string, mixed> $options
     *
     * @return iterable<int, TEntity>
     *
     * @throws Exception\QueryServiceException
     */
    public function findMany(array $criteria, array $options = []): iterable;

    /**
     * @param AbstractQuery|QueryBuilder $queryOrBuilder
     * @param array<string, mixed> $options
     *
     * @return TEntity|array<mixed>|null
     *
     * @throws Exception\QueryServiceException
     */
    public function getSingleResultOrNull(
        AbstractQuery|QueryBuilder $queryOrBuilder,
        array $options = []
    ): EntityInterface|array|null;

    /**
     * @param AbstractQuery|QueryBuilder $queryOrBuilder
     * @param array<string, mixed> $options
     *
     * @return int|float|bool|string|null
     *
     * @throws Exception\QueryServiceException
     */
    public function getSingleScalarResult(
        AbstractQuery|QueryBuilder $queryOrBuilder,
        array $options = []
    ): int|float|bool|string|null;

    /**
     * @param array<string, mixed> $options
     *
     * @throws Exception\QueryServiceException
     */
    public function execute(AbstractQuery|QueryBuilder $queryOrBuilder, array $options = []): mixed;

    /**
     * @param array<string, mixed> $criteria
     *
     * @throws Exception\QueryServiceException
     */
    public function count(array $criteria): int;

    /**
     * Return a new query builder instance.
     *
     * @param string|null $alias The optional query builder alias.
     *
     * @return QueryBuilder
     */
    public function createQueryBuilder(string $alias = null): QueryBuilder;
}
