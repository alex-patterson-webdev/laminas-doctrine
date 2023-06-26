<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Repository\Persistence;

use Arp\Entity\EntityInterface;
use Arp\LaminasDoctrine\Repository\Persistence\Exception\PersistenceException;

/**
 * @template TEntity of EntityInterface
 */
interface PersistServiceInterface extends TransactionServiceInterface
{
    /**
     * @param TEntity $entity
     * @param array<string, mixed> $options
     *
     * @return TEntity
     *
     * @throws PersistenceException
     */
    public function save(EntityInterface $entity, array $options = []): EntityInterface;

    /**
     * @param iterable<TEntity> $collection
     * @param array<string|int, mixed> $options
     *
     * @return iterable<int, TEntity>
     *
     * @throws PersistenceException
     */
    public function saveCollection(iterable $collection, array $options = []): iterable;

    /**
     * @param TEntity $entity
     * @param array<string, mixed> $options
     *
     * @throws PersistenceException
     */
    public function delete(EntityInterface $entity, array $options = []): bool;

    /**
     * @param iterable<TEntity> $collection
     * @param array<string, mixed> $options
     *
     * @throws PersistenceException
     */
    public function deleteCollection(iterable $collection, array $options = []): int;

    /**
     * @throws PersistenceException
     */
    public function flush(): void;

    /**
     * @throws PersistenceException
     */
    public function clear(): void;

    /**
     * @param TEntity $entity
     *
     * @throws PersistenceException
     */
    public function refresh(EntityInterface $entity): void;
}
