<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Repository;

use Arp\Entity\EntityInterface;
use Arp\LaminasDoctrine\Repository\Exception\EntityNotFoundException;
use Arp\LaminasDoctrine\Repository\Exception\EntityRepositoryException;
use Arp\LaminasDoctrine\Repository\Persistence\Exception\PersistenceException;
use Arp\LaminasDoctrine\Repository\Persistence\PersistServiceInterface;
use Arp\LaminasDoctrine\Repository\Query\Exception\QueryServiceException;
use Arp\LaminasDoctrine\Repository\Query\QueryServiceInterface;
use Arp\LaminasDoctrine\Repository\Query\QueryServiceOption;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Psr\Log\LoggerInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Repository
 */
abstract class EntityRepository implements EntityRepositoryInterface
{
    /**
     * @var class-string<EntityInterface>
     */
    protected string $entityName;

    /**
     * @var QueryServiceInterface
     */
    protected QueryServiceInterface $queryService;

    /**
     * @var PersistServiceInterface
     */
    protected PersistServiceInterface $persistService;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @param class-string<EntityInterface> $entityName
     * @param QueryServiceInterface         $queryService
     * @param PersistServiceInterface       $persistService
     * @param LoggerInterface               $logger
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
     * @param string|int $id
     *
     * @return EntityInterface|null
     *
     * @throws EntityRepositoryException
     */
    public function find($id): ?EntityInterface
    {
        try {
            return $this->queryService->findOneById($id);
        } catch (QueryServiceException $e) {
            $errorMessage = sprintf('Unable to find entity of type \'%s\': %s', $this->entityName, $e->getMessage());

            $this->logger->error($errorMessage, ['exception' => $e, 'id' => $id]);

            throw new EntityRepositoryException($errorMessage, $e->getCode(), $e);
        }
    }

    /**
     * Return a single entity instance matching the provided $criteria.
     *
     * @param array<mixed> $criteria The entity filter criteria.
     *
     * @return EntityInterface|null
     *
     * @throws EntityRepositoryException
     */
    public function findOneBy(array $criteria): ?EntityInterface
    {
        try {
            return $this->queryService->findOne($criteria);
        } catch (QueryServiceException $e) {
            $errorMessage = sprintf('Unable to find entity of type \'%s\': %s', $this->entityName, $e->getMessage());

            $this->logger->error($errorMessage, ['exception' => $e, 'criteria' => $criteria]);

            throw new EntityRepositoryException($errorMessage, $e->getCode(), $e);
        }
    }

    /**
     * Return all the entities within the collection.
     *
     * @return EntityInterface[]|iterable<int, EntityInterface>
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
     * @param array<mixed>      $criteria
     * @param array<mixed>|null $orderBy
     * @param int|null          $limit
     * @param int|null          $offset
     *
     * @return EntityInterface[]|iterable
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
            $errorMessage = sprintf(
                'Unable to return a collection of type \'%s\': %s',
                $this->entityName,
                $e->getMessage()
            );

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
     * @param EntityInterface $entity
     * @param array<mixed>    $options
     *
     * @return EntityInterface
     *
     * @throws EntityRepositoryException
     */
    public function save(EntityInterface $entity, array $options = []): EntityInterface
    {
        try {
            return $this->persistService->save($entity, $options);
        } catch (PersistenceException $e) {
            $errorMessage = sprintf('Unable to save entity of type \'%s\': %s', $this->entityName, $e->getMessage());

            $this->logger->error($errorMessage, ['exception' => $e, 'entity_name' => $this->entityName]);

            throw new EntityRepositoryException($errorMessage, $e->getCode(), $e);
        }
    }

    /**
     * Save a collection of entities in a single transaction
     *
     * @param iterable<EntityInterface> $collection The collection of entities that should be saved.
     * @param array<mixed>              $options    the optional save options.
     *
     * @return iterable<EntityInterface>
     *
     * @throws EntityRepositoryException If the save cannot be completed
     */
    public function saveCollection(iterable $collection, array $options = []): iterable
    {
        try {
            return $this->persistService->saveCollection($collection, $options);
        } catch (PersistenceException $e) {
            $errorMessage = sprintf('Unable to save entity of type \'%s\': %s', $this->entityName, $e->getMessage());

            $this->logger->error($errorMessage, ['exception' => $e, 'entity_name' => $this->entityName]);

            throw new EntityRepositoryException($errorMessage, $e->getCode(), $e);
        }
    }

    /**
     * @param EntityInterface|string|int|mixed $entity
     * @param array<mixed>                     $options
     *
     * @return bool
     *
     * @throws EntityRepositoryException
     */
    public function delete($entity, array $options = []): bool
    {
        if (is_string($entity) || is_int($entity)) {
            $id = $entity;
            $entity = $this->find($id);

            if (null === $entity) {
                $errorMessage = sprintf(
                    'Unable to delete entity \'%s::%s\': The entity could not be found',
                    $this->entityName,
                    $id
                );

                $this->logger->error($errorMessage);

                throw new EntityNotFoundException($errorMessage);
            }
        } elseif (!$entity instanceof EntityInterface) {
            $errorMessage = sprintf(
                'The \'entity\' argument must be a \'string\' or an object of type \'%s\'; '
                . '\'%s\' provided in \'%s::%s\'',
                EntityInterface::class,
                (is_object($entity) ? get_class($entity) : gettype($entity)),
                static::class,
                __FUNCTION__
            );

            $this->logger->error($errorMessage);

            throw new EntityRepositoryException($errorMessage);
        }


        try {
            return $this->persistService->delete($entity, $options);
        } catch (\Exception $e) {
            $errorMessage = sprintf(
                'Unable to delete entity of type \'%s\': %s',
                $this->entityName,
                $e->getMessage()
            );

            $this->logger->error($errorMessage, ['exception' => $e, 'entity_name' => $this->entityName]);

            throw new EntityRepositoryException($errorMessage, $e->getCode(), $e);
        }
    }

    /**
     * Perform a deletion of a collection of entities
     *
     * @param iterable<EntityInterface> $collection
     * @param array<mixed>              $options
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
            $errorMessage = sprintf(
                'Unable to delete entity collection of type \'%s\': %s',
                $this->entityName,
                $e->getMessage()
            );

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
            $errorMessage = sprintf(
                'Unable to clear entity of type \'%s\': %s',
                $this->entityName,
                $e->getMessage()
            );

            $this->logger->error($errorMessage, ['exception' => $e, 'entity_name' => $this->entityName]);

            throw new EntityRepositoryException($errorMessage, $e->getCode(), $e);
        }
    }

    /**
     * @param EntityInterface $entity
     *
     * @throws EntityRepositoryException
     */
    public function refresh(EntityInterface $entity): void
    {
        try {
            $this->persistService->refresh($entity);
        } catch (PersistenceException $e) {
            $errorMessage = sprintf(
                'Unable to refresh entity of type \'%s\': %s',
                $this->entityName,
                $e->getMessage()
            );

            $this->logger->error(
                $errorMessage,
                ['exception' => $e, 'entity_name' => $this->entityName, 'id' => $entity->getId()]
            );

            throw new EntityRepositoryException($errorMessage, $e->getCode(), $e);
        }
    }

    /**
     * Execute query builder or query instance and return the results.
     *
     * @param object|QueryBuilder|AbstractQuery $query
     * @param array<mixed>                      $options
     *
     * @return EntityInterface[]|iterable
     *
     * @throws EntityRepositoryException
     */
    protected function executeQuery(object $query, array $options = [])
    {
        try {
            return $this->queryService->execute($query, $options);
        } catch (QueryServiceException $e) {
            $errorMessage = sprintf(
                'Failed to perform query for entity type \'%s\': %s',
                $this->entityName,
                $e->getMessage()
            );

            $this->logger->error($errorMessage, ['exception' => $e]);

            throw new EntityRepositoryException($errorMessage, $e->getCode(), $e);
        }
    }

    /**
     * Return a single entity instance. NULL will be returned if the result set contains 0 or more than 1 result.
     *
     * Optionally control the object hydration with QueryServiceOption::HYDRATE_MODE.
     *
     * @param object|AbstractQuery|QueryBuilder $query
     * @param array<string, mixed>              $options
     *
     * @return array<mixed>|EntityInterface|null
     *
     * @throws EntityRepositoryException
     */
    protected function getSingleResultOrNull(object $query, array $options = [])
    {
        try {
            return $this->queryService->getSingleResultOrNull($query, $options);
        } catch (QueryServiceException $e) {
            $errorMessage = sprintf(
                'Failed to perform query for entity type \'%s\': %s',
                $this->entityName,
                $e->getMessage()
            );

            $this->logger->error(
                $errorMessage,
                ['exception' => $e, 'entity_name' => $this->entityName]
            );

            throw new EntityRepositoryException($errorMessage, $e->getCode(), $e);
        }
    }

    /**
     * Return a result set containing a single array result. NULL will be returned if the result set
     * contains 0 or more than 1 result.
     *
     * @param object       $query
     * @param array<mixed> $options
     *
     * @return array<mixed>|null
     *
     * @throws EntityRepositoryException
     */
    protected function getSingleArrayResultOrNull(object $query, array $options = []): ?array
    {
        $result = $this->getSingleResultOrNull(
            $query,
            array_replace_recursive(
                $options,
                [QueryServiceOption::HYDRATION_MODE => AbstractQuery::HYDRATE_ARRAY]
            )
        );

        return is_array($result) ? $result : null;
    }
}
