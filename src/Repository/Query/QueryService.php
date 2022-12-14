<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Repository\Query;

use Arp\Entity\EntityInterface;
use Arp\LaminasDoctrine\Repository\Query\Exception\QueryServiceException;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\TransactionRequiredException;
use Psr\Log\LoggerInterface;

/**
 * @implements QueryServiceInterface<EntityInterface>
 */
class QueryService implements QueryServiceInterface
{
    /**
     * @var class-string<EntityInterface>
     */
    protected string $entityName;

    protected EntityManagerInterface $entityManager;

    protected LoggerInterface $logger;

    /**
     * @param class-string<EntityInterface> $entityName
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface $logger
     */
    public function __construct(string $entityName, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->entityName = $entityName;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * @return class-string<EntityInterface>
     */
    public function getEntityName(): string
    {
        return $this->entityName;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return EntityInterface|null|array<mixed>
     *
     * @throws QueryServiceException
     */
    public function getSingleResultOrNull(
        AbstractQuery|QueryBuilder $queryOrBuilder,
        array $options = []
    ): EntityInterface|array|null {
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
     * @param AbstractQuery|QueryBuilder $queryOrBuilder
     * @param array<string, mixed> $options
     *
     * @return int|float|bool|string|null
     *
     * @throws QueryServiceException
     */
    public function getSingleScalarResult(
        AbstractQuery|QueryBuilder $queryOrBuilder,
        array $options = []
    ): int|float|bool|string|null {
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
     * @param array<string, mixed> $options
     *
     * @throws QueryServiceException
     */
    public function execute(AbstractQuery|QueryBuilder $queryOrBuilder, array $options = []): mixed
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
     * @param array<string, mixed> $options
     *
     * @return EntityInterface|null
     *
     * @throws QueryServiceException
     */
    public function findOneById(int $id, array $options = []): ?EntityInterface
    {
        return $this->findOne(compact('id'), $options);
    }

    /**
     * @param array<string, mixed> $criteria
     * @param array<string, mixed> $options
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
     * @param array<string, mixed> $criteria
     * @param array<string, mixed> $options
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
     * @param array<string, mixed> $criteria
     *
     * @throws QueryServiceException
     */
    public function count(array $criteria): int
    {
        $unitOfWork = $this->entityManager->getUnitOfWork();

        try {
            return $unitOfWork->getEntityPersister($this->entityName)->count($criteria);
        } catch (\Exception $e) {
            $errorMessage = sprintf('Failed to execute \'count\' query for entity \'%s\'', $this->entityName);

            $this->logger->error($errorMessage, ['exception' => $e, 'criteria' => $criteria]);


            throw new QueryServiceException($errorMessage, $e->getCode(), $e);
        }
    }

    /**
     * @param array<string, mixed> $options
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
     * @param array<string, mixed> $options
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

    public function createQueryBuilder(string $alias = null): QueryBuilder
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();

        if (null !== $alias) {
            $queryBuilder->select($alias)->from($this->entityName, $alias);
        }

        return $queryBuilder;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @throws QueryServiceException
     */
    private function getQuery(AbstractQuery|QueryBuilder $queryOrBuilder, array $options = []): AbstractQuery
    {
        if ($queryOrBuilder instanceof QueryBuilder) {
            $queryOrBuilder = $this->prepareQueryBuilder($queryOrBuilder, $options)->getQuery();
        }

        return $this->prepareQuery($queryOrBuilder, $options);
    }
}
