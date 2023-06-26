<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Repository\Persistence;

use Arp\Entity\EntityInterface;
use Arp\LaminasDoctrine\Repository\Persistence\Exception\PersistenceException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * @implements PersistServiceInterface<EntityInterface>
 */
class PersistService implements PersistServiceInterface
{
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param array<string|int, mixed> $options
     *
     * @throws PersistenceException
     */
    public function save(EntityInterface $entity, array $options = []): EntityInterface
    {
        if ($entity->hasId()) {
            return $this->update($entity, $options);
        }
        return $this->insert($entity, $options);
    }

    /**
     * @param iterable<EntityInterface> $collection
     * @param array<string|int, mixed>  $options
     *
     * @return iterable<EntityInterface>
     *
     * @throws PersistenceException
     */
    public function saveCollection(iterable $collection, array $options = []): iterable
    {
        $transaction = (bool)($options['transaction'] ?? true);
        $flush = (bool)($options['flush'] ?? true);

        try {
            if ($transaction) {
                $this->beginTransaction();
            }

            $saveOptions = array_replace_recursive(
                [
                    'flush' => !$flush,
                    'transaction' => !$transaction,
                ],
                $options['entity_options'] ?? []
            );

            foreach ($collection as $entity) {
                $this->save($entity, $saveOptions);
            }

            if ($flush) {
                $this->entityManager->flush();
            }

            if ($transaction) {
                $this->commitTransaction();
            }

            return $collection;
        } catch (PersistenceException $e) {
            if ($transaction) {
                $this->rollbackTransaction();
            }
            throw $e;
        } catch (\Throwable $e) {
            if ($transaction) {
                $this->rollbackTransaction();
            }

            $this->logger->error($e->getMessage(), ['exception' => $e]);

            throw new PersistenceException('Failed to save collection', $e->getCode(), $e);
        }
    }

    /**
     * @param array<string|int, mixed> $options
     *
     * @throws PersistenceException
     */
    private function update(EntityInterface $entity, array $options = []): EntityInterface
    {
        $transaction = (bool)($options['transaction'] ?? false);
        $flush = (bool)($options['flush'] ?? true);

        try {
            if ($transaction) {
                $this->beginTransaction();
            }

            if ($flush) {
                $this->entityManager->flush();
            }

            if ($transaction) {
                $this->commitTransaction();
            }

            return $entity;
        } catch (PersistenceException $e) {
            $this->rollbackTransaction();
            throw $e;
        } catch (\Throwable $e) {
            if ($transaction) {
                $this->rollbackTransaction();
            }

            $this->logger->error($e->getMessage(), ['exception' => $e]);

            throw new PersistenceException('Failed to update entity', $e->getCode(), $e);
        }
    }

    /**
     * @param array<string|int, mixed> $options
     *
     * @throws PersistenceException
     */
    private function insert(EntityInterface $entity, array $options = []): EntityInterface
    {
        $transaction = (bool)($options['transaction'] ?? false);
        $flush = (bool)($options['flush'] ?? true);

        try {
            if ($transaction) {
                $this->beginTransaction();
            }

            $this->entityManager->persist($entity);

            if ($flush) {
                $this->flush();
            }

            if ($transaction) {
                $this->commitTransaction();
            }

            return $entity;
        } catch (PersistenceException $e) {
            if ($transaction) {
                $this->rollbackTransaction();
            }
            throw $e;
        } catch (\Throwable $e) {
            if ($transaction) {
                $this->rollbackTransaction();
            }

            $this->logger->error($e->getMessage(), ['exception' => $e]);

            throw new PersistenceException('Failed to insert entity', $e->getCode(), $e);
        }
    }

    /**
     * @param array<string|int, mixed> $options
     *
     * @throws PersistenceException
     */
    public function delete(EntityInterface $entity, array $options = []): bool
    {
        $transaction = (bool)($options['transaction'] ?? false);
        $flush = (bool)($options['flush'] ?? true);

        try {
            if ($transaction) {
                $this->beginTransaction();
            }

            $this->entityManager->remove($entity);

            if ($flush) {
                $this->flush();
            }

            if ($transaction) {
                $this->commitTransaction();
            }

            return true;
        } catch (PersistenceException $e) {
            if ($transaction) {
                $this->rollbackTransaction();
            }
            throw $e;
        } catch (\Throwable $e) {
            if ($transaction) {
                $this->rollbackTransaction();
            }

            $this->logger->error($e->getMessage(), ['exception' => $e]);

            throw new PersistenceException('Failed to delete entity', $e->getCode(), $e);
        }
    }

    /**
     * @param iterable<EntityInterface> $collection
     * @param array<string|int, mixed>  $options
     *
     * @throws PersistenceException
     */
    public function deleteCollection(iterable $collection, array $options = []): int
    {
        $transaction = (bool)($options['transaction'] ?? true);
        $flush = (bool)($options['flush'] ?? true);

        try {
            if ($transaction) {
                $this->beginTransaction();
            }

            $saveOptions = array_replace_recursive(
                [
                    'flush' => !$flush,
                    'transaction' => !$transaction,
                ],
                $options['entity_options'] ?? []
            );

            $deletedCount = 0;
            foreach ($collection as $entity) {
                if ($this->delete($entity, $saveOptions)) {
                    $deletedCount++;
                }
            }

            if ($flush) {
                $this->flush();
            }

            if ($transaction) {
                $this->commitTransaction();
            }

            return $deletedCount;
        } catch (\Throwable $e) {
            if ($transaction) {
                $this->beginTransaction();
            }

            $this->logger->error($e->getMessage(), ['exception' => $e]);

            throw new PersistenceException('Failed to save collection of type \'%s\'', $e->getCode(), $e);
        }
    }

    /**
     * @throws PersistenceException
     */
    public function flush(): void
    {
        try {
            $this->entityManager->flush();
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);

            throw new PersistenceException('Failed to flush', $e->getCode(), $e);
        }
    }

    /**
     * @throws PersistenceException
     */
    public function clear(): void
    {
        try {
            $this->entityManager->clear();
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);

            throw new PersistenceException('The clear  operation failed for entity \'%s\'', $e->getCode(), $e);
        }
    }

    /**
     * @throws PersistenceException
     */
    public function refresh(EntityInterface $entity): void
    {
        try {
            $this->entityManager->refresh($entity);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);

            throw new PersistenceException('The refresh operation failed', $e->getCode(), $e);
        }
    }

    /**
     * @throws PersistenceException
     */
    public function beginTransaction(): void
    {
        try {
            $this->entityManager->beginTransaction();
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);

            throw new PersistenceException('Failed to start transaction', $e->getCode(), $e);
        }
    }

    /**
     * @throws PersistenceException
     */
    public function commitTransaction(): void
    {
        try {
            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);

            throw new PersistenceException('Failed to commit transaction', $e->getCode(), $e);
        }
    }

    /**
     * @throws PersistenceException
     */
    public function rollbackTransaction(): void
    {
        try {
            $this->entityManager->rollback();
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);

            throw new PersistenceException('Failed to rollback transaction', $e->getCode(), $e);
        }
    }
}
