<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Repository\Query;

use Arp\DoctrineEntityRepository\Query\Exception\QueryServiceException;
use Arp\DoctrineEntityRepository\Query\QueryServiceInterface;
use Arp\Entity\EntityInterface;
use Arp\LaminasDoctrine\Query\Exception\QueryFilterManagerException;
use Arp\LaminasDoctrine\Query\Filter\FilterInterface;
use Arp\LaminasDoctrine\Query\QueryBuilderInterface;
use Arp\LaminasDoctrine\Query\QueryFilterManager;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;

/**
 * QueryServiceInterface which integrates the Arp\LaminasDoctrine\Query\QueryFilterManager
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Repository\Query
 */
class QueryFilterService implements QueryServiceInterface
{
    /**
     * @var string
     */
    private string $entityName;

    /**
     * @var QueryServiceInterface
     */
    private QueryServiceInterface $queryService;

    /**
     * @var QueryFilterManager
     */
    private QueryFilterManager $queryFilterManager;

    /**
     * @param string $entityName
     * @param QueryServiceInterface $queryService
     * @param QueryFilterManager    $queryFilterManager
     */
    public function __construct(
        string $entityName,
        QueryServiceInterface $queryService,
        QueryFilterManager $queryFilterManager
    ) {
        $this->entityName = $entityName;
        $this->queryService = $queryService;
        $this->queryFilterManager = $queryFilterManager;
    }

    /**
     * @param int|string $id
     * @param array      $options
     *
     * @return EntityInterface|null
     *
     * @throws QueryServiceException
     */
    public function findOneById($id, array $options = []): ?EntityInterface
    {
        return $this->queryService->findOneById($id, $options);
    }

    /**
     * @param array $criteria
     * @param array $options
     *
     * @return EntityInterface|null
     *
     * @throws QueryServiceException
     */
    public function findOne(array $criteria, array $options = []): ?EntityInterface
    {
        return $this->queryService->findOne($criteria, $options);
    }

    /**
     * @param array $criteria
     * @param array $options
     *
     * @return iterable
     *
     * @throws QueryServiceException
     */
    public function findMany(array $criteria, array $options = []): iterable
    {
        return $this->queryService->findMany($criteria, $options);
    }

    /**
     * @param AbstractQuery|QueryBuilder $queryOrBuilder
     * @param array                      $options
     *
     * @return EntityInterface|array|null
     *
     * @throws QueryServiceException
     */
    public function getSingleResultOrNull($queryOrBuilder, array $options = [])
    {
        return $this->queryService->getSingleResultOrNull($queryOrBuilder, $options);
    }

    /**
     * @param string|null $alias
     *
     * @return QueryBuilder
     */
    public function createQueryBuilder(string $alias = null): QueryBuilder
    {
        return $this->queryService->createQueryBuilder($alias);
    }

    /**
     * @param AbstractQuery|QueryBuilder $queryOrBuilder
     * @param array                      $options
     *
     * @return mixed
     *
     * @throws QueryServiceException
     */
    public function execute($queryOrBuilder, array $options = [])
    {
        return $this->queryService->execute($queryOrBuilder, $options);
    }

    /**
     * @param array $criteria
     *
     * @return mixed
     */
    public function count(array $criteria)
    {
        return $this->queryService->count($criteria);
    }

    /**
     * @return QueryFilterManager
     */
    public function getQueryFilterManager(): QueryFilterManager
    {
        return $this->queryFilterManager;
    }

    /**
     * Apply query $criteria filtering to the provided $queryBuilder
     *
     * @param array                              $filters
     * @param QueryBuilder|QueryBuilderInterface $queryBuilder
     *
     * @throws QueryServiceException
     */
    public function filter(array $filters, $queryBuilder = null): void
    {
        $queryBuilder = $queryBuilder ?? $this->createQueryBuilder();
        $criteria = [
            'filters' => $filters,
        ];

        try {
            $this->queryFilterManager->filter($queryBuilder, $this->entityName, $criteria);
        } catch (QueryFilterManagerException $e) {
            throw new QueryServiceException(
                sprintf(
                    'Failed to create query apply query filters for entity \'%s\': %s',
                    $this->entityName,
                    $e->getMessage()
                ),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @param string $name
     * @param array  $options
     *
     * @return FilterInterface
     *
     * @throws QueryServiceException
     */
    public function createQueryFilter(string $name, array $options = []): FilterInterface
    {
        try {
            return $this->queryFilterManager->createFilter($name, $options);
        } catch (QueryFilterManagerException $e) {
            throw new QueryServiceException(
                sprintf('Failed to create query filter \'%s\': %s', $name, $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }
}
