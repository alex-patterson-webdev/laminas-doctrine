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
     * @return Entity|null
     *
     * @throws EntityRepositoryException
     */
    public function findOneById(int $id): ?EntityInterface;

    /**
     * @param Entity $entity
     * @param array<mixed> $options
     *
     * @return Entity
     *
     * @throws EntityRepositoryException
     */
    public function save(EntityInterface $entity, array $options = []): EntityInterface;

    /**
     * @param iterable<Entity> $collection
     * @param array<mixed> $options
     *
     * @return iterable<Entity>
     *
     * @throws EntityRepositoryException
     */
    public function saveCollection(iterable $collection, array $options = []): iterable;

    /**
     * @param Entity $entity
     * @param array<mixed> $options
     *
     * @throws EntityRepositoryException
     */
    public function delete(EntityInterface $entity, array $options = []): bool;

    /**
     * @param iterable<Entity> $collection
     * @param array<mixed> $options
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
