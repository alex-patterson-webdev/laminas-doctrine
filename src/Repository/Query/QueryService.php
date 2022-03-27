<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Repository\Query;

use Arp\LaminasDoctrine\Repository\Query\Exception\QueryServiceException;
use Arp\Entity\EntityInterface;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\TransactionRequiredException;
use Psr\Log\LoggerInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Repository\Query
 */
class QueryService implements QueryServiceInterface
{
    /**
     * @var class-string
     */
    protected string $entityName;

    /**
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $entityManager;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @param class-string           $entityName
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface        $logger
     */
    public function __construct(string $entityName, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->entityName = $entityName;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return $this->entityName;
    }

    /**
     * @param object|AbstractQuery|QueryBuilder $queryOrBuilder
     * @param array<string, mixed>              $options
     *
     * @return EntityInterface|null|array<mixed>
     *
     * @throws QueryServiceException
     */
    public function getSingleResultOrNull(object $queryOrBuilder, array $options = [])
    {
        $result = $this->execute($queryOrBuilder, $options);

        if (empty($result)) {
            return null;
        }

        if (!is_array($result)) {
            return $result;
        }

        if (count($result) > 1) {
            return null;
        }

        return array_shift($result);
    }

    /**
     * @param object|AbstractQuery|QueryBuilder $queryOrBuilder
     * @param array<string, mixed>              $options
     *
     * @return int|mixed|string
     *
     * @throws QueryServiceException
     */
    public function getSingleScalarResult(object $queryOrBuilder, array $options = [])
    {
        try {
            return $this->getQuery($queryOrBuilder, $options)->getSingleScalarResult();
        } catch (QueryServiceException $e) {
            throw $e;
        } catch (\Exception $e) {
            $message = sprintf(
                'An error occurred while loading fetching a single scalar result: %s',
                $e->getMessage()
            );

            $this->logger->error($message, ['exception' => $e]);

            throw new QueryServiceException($message, $e->getCode(), $e);
        }
    }

    /**
     * Construct and execute the query.
     *
     * @param object|AbstractQuery|QueryBuilder $queryOrBuilder
     * @param array<string, mixed>              $options
     *
     * @return mixed
     *
     * @throws QueryServiceException
     */
    public function execute(object $queryOrBuilder, array $options = [])
    {
        try {
            return $this->getQuery($queryOrBuilder, $options)->execute();
        } catch (QueryServiceException $e) {
            throw $e;
        } catch (\Exception $e) {
            $message = sprintf('Failed to execute query : %s', $e->getMessage());

            $this->logger->error($message, ['exception' => $e]);

            throw new QueryServiceException($message, $e->getCode(), $e);
        }
    }

    /**
     * Find a single entity matching the provided identity.
     *
     * @param mixed                $id      The identity of the entity to match.
     * @param array<string, mixed> $options The optional query options.
     *
     * @return EntityInterface|null
     *
     * @throws QueryServiceException
     */
    public function findOneById($id, array $options = []): ?EntityInterface
    {
        return $this->findOne(compact('id'), $options);
    }

    /**
     * Find a single entity matching the provided criteria.
     *
     * @param array<string, mixed> $criteria The search criteria that should be matched on.
     * @param array<string, mixed> $options  The optional query options.
     *
     * @return EntityInterface|null
     *
     * @throws QueryServiceException
     */
    public function findOne(array $criteria, array $options = []): ?EntityInterface
    {
        try {
            $persist = $this->entityManager->getUnitOfWork()->getEntityPersister($this->entityName);

            $entity = $persist->load(
                $criteria,
                $options[QueryServiceOption::ENTITY] ?? null,
                $options[QueryServiceOption::ASSOCIATION] ?? null,
                $options[QueryServiceOption::HINTS] ?? [],
                $options[QueryServiceOption::LOCK_MODE] ?? null,
                1,
                $options[QueryServiceOption::ORDER_BY] ?? null
            );

            return ($entity instanceof EntityInterface) ? $entity : null;
        } catch (\Exception $e) {
            $message = sprintf('Failed to execute \'findOne\' query: %s', $e->getMessage());

            $this->logger->error($message, ['exception' => $e, 'criteria' => $criteria, 'options' => $options]);

            throw new QueryServiceException($message, $e->getCode(), $e);
        }
    }

    /**
     * Find a collection of entities that match the provided criteria.
     *
     * @param array<string, mixed> $criteria The search criteria that should be matched on.
     * @param array<string, mixed> $options  The optional query options.
     *
     * @return iterable<EntityInterface>
     *
     * @throws QueryServiceException
     */
    public function findMany(array $criteria, array $options = []): iterable
    {
        try {
            $persister = $this->entityManager->getUnitOfWork()->getEntityPersister($this->entityName);

            return $persister->loadAll(
                $criteria,
                $options[QueryServiceOption::ORDER_BY] ?? null,
                $options[QueryServiceOption::MAX_RESULTS] ?? null,
                $options[QueryServiceOption::FIRST_RESULT] ?? null
            );
        } catch (\Exception $e) {
            $message = sprintf('Failed to execute \'findMany\' query: %s', $e->getMessage());

            $this->logger->error($message, ['exception' => $e, 'criteria' => $criteria, 'options' => $options]);

            throw new QueryServiceException($message, $e->getCode(), $e);
        }
    }

    /**
     * Return the result set count.
     *
     * @param array<string, mixed> $criteria
     *
     * @return int
     */
    public function count(array $criteria): int
    {
        $unitOfWork = $this->entityManager->getUnitOfWork();

        return $unitOfWork->getEntityPersister($this->entityName)->count($criteria);
    }

    /**
     * Set the query builder options.
     *
     * @param QueryBuilder         $queryBuilder The query builder to update.
     * @param array<string, mixed> $options      The query builder options to set.
     *
     * @return QueryBuilder
     */
    protected function prepareQueryBuilder(QueryBuilder $queryBuilder, array $options = []): QueryBuilder
    {
        if (isset($options[QueryServiceOption::FIRST_RESULT])) {
            $queryBuilder->setFirstResult($options[QueryServiceOption::FIRST_RESULT]);
        }

        if (isset($options[QueryServiceOption::MAX_RESULTS])) {
            $queryBuilder->setMaxResults($options[QueryServiceOption::MAX_RESULTS]);
        }

        if (isset($options[QueryServiceOption::ORDER_BY]) && is_array($options[QueryServiceOption::ORDER_BY])) {
            foreach ($options[QueryServiceOption::ORDER_BY] as $fieldName => $orderDirection) {
                $queryBuilder->addOrderBy(
                    $fieldName,
                    ('DESC' === strtoupper($orderDirection) ? 'DESC' : 'ASC')
                );
            }
        }

        return $queryBuilder;
    }

    /**
     * Prepare the provided query by setting the $options.
     *
     * @param AbstractQuery        $query
     * @param array<string, mixed> $options
     *
     * @return AbstractQuery
     *
     * @throws QueryServiceException
     */
    protected function prepareQuery(AbstractQuery $query, array $options = []): AbstractQuery
    {
        if (isset($options['params'])) {
            $query->setParameters($options['params']);
        }

        if (isset($options[QueryServiceOption::HYDRATION_MODE])) {
            $query->setHydrationMode($options[QueryServiceOption::HYDRATION_MODE]);
        }

        if (isset($options['result_set_mapping'])) {
            $query->setResultSetMapping($options['result_set_mapping']);
        }

        if (isset($options[QueryServiceOption::HINTS]) && is_array($options[QueryServiceOption::HINTS])) {
            foreach ($options[QueryServiceOption::HINTS] as $hint => $hintValue) {
                $query->setHint($hint, $hintValue);
            }
        }

        if ($query instanceof Query) {
            if (!empty($options[QueryServiceOption::DQL])) {
                $query->setDQL($options[QueryServiceOption::DQL]);
            }

            if (isset($options[QueryServiceOption::LOCK_MODE])) {
                try {
                    $query->setLockMode($options[QueryServiceOption::LOCK_MODE]);
                } catch (TransactionRequiredException $e) {
                    throw new QueryServiceException($e->getMessage(), $e->getCode(), $e);
                }
            }
        }

        return $query;
    }

    /**
     * Return a new query builder instance.
     *
     * @param string|null $alias The optional query builder alias.
     *
     * @return QueryBuilder
     */
    public function createQueryBuilder(string $alias = null): QueryBuilder
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();

        if (null !== $alias) {
            $queryBuilder->select($alias)->from($this->entityName, $alias);
        }

        return $queryBuilder;
    }

    /**
     * Resolve the ORM Query instance for a QueryBuilder and set the optional $options
     *
     * @param object|AbstractQuery|QueryBuilder $queryOrBuilder
     * @param array<mixed>               $options
     *
     * @return AbstractQuery
     *
     * @throws QueryServiceException
     */
    private function getQuery(object $queryOrBuilder, array $options = []): AbstractQuery
    {
        if (!$queryOrBuilder instanceof AbstractQuery && !$queryOrBuilder instanceof QueryBuilder) {
            throw new QueryServiceException(
                sprintf(
                    'The queryOrBuilder argument must be an object of type '
                    . '\'%s\' or \'%s\'; \'%s\' provided in \'%s\'.',
                    AbstractQuery::class,
                    QueryBuilder::class,
                    get_class($queryOrBuilder),
                    __METHOD__
                )
            );
        }

        if ($queryOrBuilder instanceof QueryBuilder) {
            $query = $this->prepareQueryBuilder($queryOrBuilder, $options)->getQuery();
        } else {
            $query = $queryOrBuilder;
        }

        return $this->prepareQuery($query, $options);
    }
}
