<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Repository\Query;

use Arp\Entity\EntityInterface;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;

/**
 * @template TEntity as EntityInterface
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Repository\Query
 */
interface QueryServiceInterface
{
    /**
     * @return class-string<TEntity>
     */
    public function getEntityName(): string;

    /**
     * Find a single entity matching the provided identity.
     *
     * @param int|string           $id      The identity of the entity to match.
     * @param array<string, mixed> $options The optional query options.
     *
     * @return TEntity|null
     *
     * @throws Exception\QueryServiceException
     */
    public function findOneById($id, array $options = []): ?EntityInterface;

    /**
     * Find a single entity matching the provided criteria.
     *
     * @param array<string, mixed> $criteria The search criteria that should be matched on.
     * @param array<string, mixed> $options  The optional query options.
     *
     * @return TEntity|null
     *
     * @throws Exception\QueryServiceException
     */
    public function findOne(array $criteria, array $options = []): ?EntityInterface;

    /**
     * Find a collection of entities that match the provided criteria.
     *
     * @param array<string, mixed> $criteria The search criteria that should be matched on.
     * @param array<string, mixed> $options  The optional query options.
     *
     * @return TEntity[]|iterable
     *
     * @throws Exception\QueryServiceException
     */
    public function findMany(array $criteria, array $options = []): iterable;

    /**
     * @param object|AbstractQuery|QueryBuilder $queryOrBuilder
     * @param array<string, mixed>              $options
     *
     * @return TEntity|array<mixed>|null
     *
     * @throws Exception\QueryServiceException
     */
    public function getSingleResultOrNull(object $queryOrBuilder, array $options = []);

    /**
     * @param object|AbstractQuery|QueryBuilder $queryOrBuilder
     * @param array<string, mixed>              $options
     *
     * @return int|float|bool|string|null
     *
     * @throws Exception\QueryServiceException
     */
    public function getSingleScalarResult(object $queryOrBuilder, array $options = []);

    /**
     * Construct and execute the query.
     *
     * @param object|AbstractQuery|QueryBuilder $queryOrBuilder
     * @param array<string, mixed>              $options
     *
     * @return mixed
     *
     * @throws Exception\QueryServiceException
     */
    public function execute(object $queryOrBuilder, array $options = []);

    /**
     * Return the result set count.
     *
     * @param array<string, mixed> $criteria
     *
     * @return mixed
     */
    public function count(array $criteria);

    /**
     * Return a new query builder instance.
     *
     * @param string|null $alias The optional query builder alias.
     *
     * @return QueryBuilder
     */
    public function createQueryBuilder(string $alias = null): QueryBuilder;
}
