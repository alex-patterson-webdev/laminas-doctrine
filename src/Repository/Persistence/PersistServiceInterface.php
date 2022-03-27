<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Repository\Persistence;

use Arp\Entity\EntityInterface;
use Arp\LaminasDoctrine\Repository\Persistence\Exception\PersistenceException;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Persistence
 */
interface PersistServiceInterface extends TransactionServiceInterface
{
    /**
     * Return the full qualified class name of the entity
     *
     * @return class-string
     */
    public function getEntityName(): string;

    /**
     * Create or update a entity instance
     *
     * @param EntityInterface      $entity  The entity instance that should be saved
     * @param array<string, mixed> $options The optional save options
     *
     * @return EntityInterface
     *
     * @throws PersistenceException  If the entity cannot be saved.
     */
    public function save(EntityInterface $entity, array $options = []): EntityInterface;

    /**
     * @param iterable<EntityInterface> $collection The collection of entities that should be saved
     * @param array<string|int, mixed>  $options    the optional save options
     *
     * @return iterable<EntityInterface>
     *
     * @throws PersistenceException
     */
    public function saveCollection(iterable $collection, array $options = []): iterable;

    /**
     * Delete an entity instance
     *
     * @param EntityInterface      $entity  The entity that should be deleted
     * @param array<string, mixed> $options The optional deletion options
     *
     * @return boolean
     *
     * @throws PersistenceException  If the collection cannot be deleted.
     */
    public function delete(EntityInterface $entity, array $options = []): bool;

    /**
     * Perform a deletion of a collection of entities
     *
     * @param iterable<EntityInterface> $collection
     * @param array<string, mixed>      $options
     *
     * @return int
     *
     * @throws PersistenceException
     */
    public function deleteCollection(iterable $collection, array $options = []): int;

    /**
     * Perform a flush of the unit of work
     *
     * @throws PersistenceException
     */
    public function flush(): void;

    /**
     * Release managed entities from the identity map
     *
     * @return void
     *
     * @throws PersistenceException
     */
    public function clear(): void;

    /**
     * @param EntityInterface $entity
     *
     * @throws PersistenceException
     */
    public function refresh(EntityInterface $entity): void;
}
