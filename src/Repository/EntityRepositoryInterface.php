<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Repository;

use Arp\Entity\EntityInterface;
use Arp\LaminasDoctrine\Repository\Exception\EntityRepositoryException;
use Doctrine\Persistence\ObjectRepository;

/**
 * @template Entity of EntityInterface
 * @extends ObjectRepository<Entity>
 */
interface EntityRepositoryInterface extends ObjectRepository
{
    /**
     * @param int $id
     *
     * @return Entity|null
     *
     * @throws EntityRepositoryException
     */
    public function findOneById(int $id): ?EntityInterface;

    /**
     * @param Entity $entity
     * @param array<mixed> $options
     *
     * @return EntityInterface
     *
     * @throws EntityRepositoryException
     */
    public function save(EntityInterface $entity, array $options = []): EntityInterface;

    /**
     * Save a collection of entities in a single transaction
     *
     * @param iterable<Entity> $collection The collection of entities that should be saved.
     * @param array<mixed> $options                 the optional save options.
     *
     * @return iterable<Entity>
     *
     * @throws EntityRepositoryException If the save cannot be completed
     */
    public function saveCollection(iterable $collection, array $options = []): iterable;

    /**
     * Delete an entity
     *
     * @param Entity $entity
     * @param array<mixed> $options
     *
     * @return bool
     *
     * @throws EntityRepositoryException
     */
    public function delete(EntityInterface $entity, array $options = []): bool;

    /**
     * Perform a deletion of a collection of entities
     *
     * @param iterable<Entity> $collection
     * @param array<mixed> $options
     *
     * @return int
     *
     * @throws EntityRepositoryException
     */
    public function deleteCollection(iterable $collection, array $options = []): int;

    /**
     * @throws EntityRepositoryException
     */
    public function clear(): void;

    /**
     * @param Entity $entity
     *
     * @throws EntityRepositoryException
     */
    public function refresh(EntityInterface $entity): void;
}
