<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Repository;

use Arp\Entity\EntityInterface;
use Arp\LaminasDoctrine\Repository\Exception\EntityRepositoryException;
use Arp\LaminasDoctrine\Repository\Persistence\Exception\PersistenceException;
use Arp\LaminasDoctrine\Repository\Persistence\PersistServiceInterface;
use Arp\LaminasDoctrine\Repository\Persistence\TransactionServiceInterface;
use Arp\LaminasDoctrine\Repository\Query\Exception\QueryServiceException;
use Arp\LaminasDoctrine\Repository\Query\QueryServiceInterface;
use Arp\LaminasDoctrine\Repository\Query\QueryServiceOption;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Psr\Log\LoggerInterface;

/**
 * @template Entity of EntityInterface
 * @implements EntityRepositoryInterface<EntityInterface>
 */
class EntityRepository implements EntityRepositoryInterface, TransactionServiceInterface
{
    /**
     * @var class-string<EntityInterface>
     */
    protected string $entityName;

    /**
     * @var QueryServiceInterface<Entity>
     */
    protected QueryServiceInterface $queryService;

    /**
     * @var PersistServiceInterface<Entity>
     */
    protected PersistServiceInterface $persistService;

    protected LoggerInterface $logger;

    /**
     * @param class-string<EntityInterface> $entityName
     * @param QueryServiceInterface<Entity> $queryService
     * @param PersistServiceInterface<Entity> $persistService
     * @param LoggerInterface $logger
     */
    public function __construct(
        string $entityName,
        QueryServiceInterface $queryService,
        PersistServiceInterface $persistService,
        LoggerInterface $logger
    ) {
        $this->entityName = $entityName;
        $this->queryService = $queryService;
        $this->persistService = $persistService;
        $this->logger = $logger;
    }

    /**
     * Return the fully qualified class name of the mapped entity instance.
     *
     * @return class-string<EntityInterface>
     */
    public function getClassName(): string
    {
        return $this->entityName;
    }

    /**
     * Return a single entity instance matching the provided $id.
     *
     * @param int $id
     *
     * @return Entity|null
     *
     * @throws EntityRepositoryException
     */
    public function find($id): ?EntityInterface
    {
        try {
            return $this->queryService->findOneById($id);
        } catch (QueryServiceException $e) {
            $errorMessage = sprintf('Unable to find entity of type \'%s\'', $this->entityName);

            $this->logger->error($errorMessage, ['exception' => $e, 'id' => $id]);

            throw new EntityRepositoryException($errorMessage, $e->getCode(), $e);
        }
    }

    /**
     * @param int $id
     *
     * @return Entity|null
     *
     * @throws EntityRepositoryException
     */
    public function findOneById(int $id): ?EntityInterface
    {
        return $this->find($id);
    }

    /**
     * Return a single entity instance matching the provided $criteria.
     *
     * @param array<mixed> $criteria The entity filter criteria.
     *
     * @return Entity|null
     *
     * @throws EntityRepositoryException
     */
    public function findOneBy(array $criteria): ?EntityInterface
    {
        try {
            return $this->queryService->findOne($criteria);
        } catch (QueryServiceException $e) {
            $errorMessage = sprintf('Unable to find entity of type \'%s\'', $this->entityName);

            $this->logger->error($errorMessage, ['exception' => $e, 'criteria' => $criteria]);

            throw new EntityRepositoryException($errorMessage, $e->getCode(), $e);
        }
    }

    /**
     * Return all the entities within the collection.
     *
     * @return iterable<int, Entity>
     *
     * @throws EntityRepositoryException
     */
    public function findAll(): iterable
    {
        return $this->findBy([]);
    }

    /**
     * Return a collection of entities that match the provided $criteria.
     *
     * @param array<mixed> $criteria
     * @param array<mixed>|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return iterable<int, Entity>
     *
     * @throws EntityRepositoryException
     */
    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): iterable
    {
        $options = [];

        try {
            if (null !== $orderBy) {
                $options[QueryServiceOption::ORDER_BY] = $orderBy;
            }

            if (null !== $limit) {
                $options[QueryServiceOption::MAX_RESULTS] = $limit;
            }

            if (null !== $offset) {
                $options[QueryServiceOption::FIRST_RESULT] = $offset;
            }

            return $this->queryService->findMany($criteria, $options);
        } catch (QueryServiceException $e) {
            $errorMessage = sprintf('Unable to return a collection of type \'%s\'', $this->entityName);

            $this->logger->error(
                $errorMessage,
                ['exception' => $e, 'criteria' => $criteria, 'options' => $options]
            );

            throw new EntityRepositoryException($errorMessage, $e->getCode(), $e);
        }
    }

    /**
     * Save a single entity instance
     *
     * @param Entity $entity
     * @param array<mixed> $options
     *
     * @return Entity
     *
     * @throws EntityRepositoryException
     */
    public function save(EntityInterface $entity, array $options = []): EntityInterface
    {
        try {
            return $this->persistService->save($entity, $options);
        } catch (PersistenceException $e) {
            $errorMessage = sprintf('Unable to save entity of type \'%s\'', $this->entityName);

            $this->logger->error($errorMessage, ['exception' => $e, 'entity_name' => $this->entityName]);

            throw new EntityRepositoryException($errorMessage, $e->getCode(), $e);
        }
    }

    /**
     * Save a collection of entities in a single transaction
     *
     * @param iterable<Entity> $collection The collection of entities that should be saved.
     * @param array<mixed> $options        the optional save options.
     *
     * @return iterable<Entity>
     *
     * @throws EntityRepositoryException If the save cannot be completed
     */
    public function saveCollection(iterable $collection, array $options = []): iterable
    {
        try {
            return $this->persistService->saveCollection($collection, $options);
        } catch (PersistenceException $e) {
            $errorMessage = sprintf('Unable to save entity of type \'%s\'', $this->entityName);

            $this->logger->error($errorMessage, ['exception' => $e, 'entity_name' => $this->entityName]);

            throw new EntityRepositoryException($errorMessage, $e->getCode(), $e);
        }
    }

    /**
     * @param Entity $entity
     * @param array<mixed> $options
     *
     * @return bool
     *
     * @throws EntityRepositoryException
     */
    public function delete(EntityInterface $entity, array $options = []): bool
    {
        try {
            return $this->persistService->delete($entity, $options);
        } catch (\Exception $e) {
            $errorMessage = sprintf(
                'Unable to delete entity of type \'%s\'',
                $this->entityName
            );

            $this->logger->error($errorMessage, ['exception' => $e, 'entity_name' => $this->entityName]);

            throw new EntityRepositoryException($errorMessage, $e->getCode(), $e);
        }
    }

    /**
     * Perform a deletion of a collection of entities
     *
     * @param iterable<int, Entity> $collection
     * @param array<mixed> $options
     *
     * @return int
     *
     * @throws EntityRepositoryException
     */
    public function deleteCollection(iterable $collection, array $options = []): int
    {
        try {
            return $this->persistService->deleteCollection($collection, $options);
        } catch (PersistenceException $e) {
            $errorMessage = sprintf('Unable to delete entity collection of type \'%s\'', $this->entityName);

            $this->logger->error($errorMessage, ['exception' => $e, 'entity_name' => $this->entityName]);

            throw new EntityRepositoryException($errorMessage, $e->getCode(), $e);
        }
    }

    /**
     * @throws EntityRepositoryException
     */
    public function clear(): void
    {
        try {
            $this->persistService->clear();
        } catch (PersistenceException $e) {
            $errorMessage = sprintf('Unable to clear entity of type \'%s\'', $this->entityName);

            $this->logger->error($errorMessage, ['exception' => $e, 'entity_name' => $this->entityName]);

            throw new EntityRepositoryException($errorMessage, $e->getCode(), $e);
        }
    }

    /**
     * @param Entity $entity
     *
     * @throws EntityRepositoryException
     */
    public function refresh(EntityInterface $entity): void
    {
        try {
            $this->persistService->refresh($entity);
        } catch (PersistenceException $e) {
            $errorMessage = sprintf('Unable to refresh entity of type \'%s\'', $this->entityName);

            $this->logger->error(
                $errorMessage,
                ['exception' => $e, 'entity_name' => $this->entityName, 'id' => $entity->getId()]
            );

            throw new EntityRepositoryException($errorMessage, $e->getCode(), $e);
        }
    }

    /**
     * @throws EntityRepositoryException
     */
    public function beginTransaction(): void
    {
        try {
            $this->persistService->beginTransaction();
        } catch (\Exception $e) {
            throw new EntityRepositoryException(
                sprintf('Failed to start transaction for entity \'%s\'', $this->entityName),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @throws EntityRepositoryException
     */
    public function commitTransaction(): void
    {
        try {
            $this->persistService->commitTransaction();
        } catch (\Exception $e) {
            throw new EntityRepositoryException(
                sprintf('Failed to commit transaction for entity \'%s\'', $this->entityName),
                $e->getCode(),
                $e
            );
        }
    }

    public function rollbackTransaction(): void
    {
        $this->persistService->rollbackTransaction();
    }

    /**
     * @param QueryBuilder|AbstractQuery $query
     * @param array<mixed> $options
     *
     * @return mixed
     *
     * @throws EntityRepositoryException
     */
    protected function executeQuery(QueryBuilder|AbstractQuery $query, array $options = []): mixed
    {
        try {
            return $this->queryService->execute($query, $options);
        } catch (QueryServiceException $e) {
            $errorMessage = sprintf('Failed to perform query for entity type \'%s\'', $this->entityName);

            $this->logger->error($errorMessage, ['exception' => $e, 'entity_name' => $this->entityName]);

            throw new EntityRepositoryException($errorMessage, $e->getCode(), $e);
        }
    }

    /**
     * @param AbstractQuery|QueryBuilder $query
     * @param array<string, mixed> $options
     *
     * @return Entity|null
     *
     * @throws EntityRepositoryException
     */
    protected function getSingleResultOrNull(AbstractQuery|QueryBuilder $query, array $options = []): ?EntityInterface
    {
        try {
            return $this->queryService->getSingleResultOrNull($query, $options);
        } catch (QueryServiceException $e) {
            $errorMessage = sprintf('Failed to perform query for entity type \'%s\'', $this->entityName);

            $this->logger->error($errorMessage, ['exception' => $e, 'entity_name' => $this->entityName]);

            throw new EntityRepositoryException($errorMessage, $e->getCode(), $e);
        }
    }

    /**
     * @param AbstractQuery|QueryBuilder $query
     * @param array<mixed> $options
     *
     * @return array<mixed>|null
     *
     * @throws EntityRepositoryException
     */
    protected function getSingleArrayResultOrNull(AbstractQuery|QueryBuilder $query, array $options = []): ?array
    {
        /** @var array<mixed>|null $result */
        $result = $this->getSingleResultOrNull(
            $query,
            array_replace_recursive(
                $options,
                [QueryServiceOption::HYDRATION_MODE => AbstractQuery::HYDRATE_ARRAY]
            )
        );

        return is_array($result) ? $result : null;
    }

    /**
     * @param AbstractQuery|QueryBuilder $query
     * @param array<string, mixed> $options
     *
     * @return int|string|float|bool|null
     *
     * @throws EntityRepositoryException
     */
    protected function getSingleScalarResult(AbstractQuery|QueryBuilder $query, array $options = []): mixed
    {
        try {
            return $this->queryService->getSingleScalarResult($query, $options);
        } catch (QueryServiceException $e) {
            $errorMessage = sprintf('Failed to perform query for entity type \'%s\'', $this->entityName);

            $this->logger->error($errorMessage, ['exception' => $e, 'entity_name' => $this->entityName]);

            throw new EntityRepositoryException($errorMessage, $e->getCode(), $e);
        }
    }
}
