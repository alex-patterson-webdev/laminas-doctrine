<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Repository;

use Arp\LaminasDoctrine\Exception\EntityRepositoryException;
use Arp\Entity\EntityInterface;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<EntityInterface>
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine
 */
interface EntityRepositoryInterface extends ObjectRepository
{
    /**
     * Save a single entity instance.
     *
     * @param EntityInterface $entity
     * @param array<mixed>    $options
     *
     * @return EntityInterface
     *
     * @throws EntityRepositoryException
     */
    public function save(EntityInterface $entity, array $options = []): EntityInterface;

    /**
     * Save a collection of entities in a single transaction.
     *
     * @param iterable<EntityInterface> $collection The collection of entities that should be saved.
     * @param array<mixed>              $options    the optional save options.
     *
     * @return iterable<EntityInterface>
     *
     * @throws EntityRepositoryException If the save cannot be completed
     */
    public function saveCollection(iterable $collection, array $options = []): iterable;

    /**
     * Delete an entity.
     *
     * @param EntityInterface|int|string $entity
     * @param array<mixed>               $options
     *
     * @return bool
     *
     * @throws EntityRepositoryException
     */
    public function delete($entity, array $options = []): bool;

    /**
     * Perform a deletion of a collection of entities.
     *
     * @param iterable<EntityInterface> $collection
     * @param array<mixed>              $options
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
     * @param EntityInterface $entity
     *
     * @throws EntityRepositoryException
     */
    public function refresh(EntityInterface $entity): void;
}
