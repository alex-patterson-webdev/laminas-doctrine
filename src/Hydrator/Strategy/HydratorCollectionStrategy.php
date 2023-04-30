<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Hydrator\Strategy;

use Arp\Entity\EntityInterface;
use Arp\LaminasDoctrine\Hydrator\Strategy\Exception\RuntimeException;
use Arp\LaminasDoctrine\Repository\EntityRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Laminas\Hydrator\HydratorInterface;
use Laminas\Hydrator\Strategy\Exception\InvalidArgumentException;

class HydratorCollectionStrategy extends AbstractHydratorStrategy implements HydrationObjectAwareInterface
{
    private string $name;

    private HydratorInterface $hydrator;

    private ?object $object;

    /**
     * @param EntityRepositoryInterface<EntityInterface> $repository
     */
    public function __construct(string $name, EntityRepositoryInterface $repository, HydratorInterface $hydrator)
    {
        parent::__construct($repository);

        $this->name = $name;
        $this->hydrator = $hydrator;
    }

    /**
     * @param mixed $value
     * @param array<string, mixed>|null $data
     *
     * @return iterable<int, EntityInterface>
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function hydrate($value, ?array $data): iterable
    {
        $entityName = $this->repository->getClassName();
        $object = $this->getObject();

        if (null === $object) {
            throw new InvalidArgumentException(
                sprintf('The hydration object has not been set for strategy \'%s\'', static::class)
            );
        }

        if (!is_iterable($value)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The \'value\' argument must be of type \'iterable\'; \'%s\' provided for entity \'%s\'',
                    gettype($value),
                    $entityName
                )
            );
        }

        $addMethodName = 'add' . ucfirst($this->name);
        if (!is_callable([$object, $addMethodName])) {
            throw new InvalidArgumentException(
                sprintf('The \'%s\' method is not callable for entity \'%s\'', $addMethodName, $entityName)
            );
        }

        $removeMethodName = 'remove' . ucfirst($this->name);
        if (!is_callable([$object, $addMethodName])) {
            throw new InvalidArgumentException(
                sprintf('The \'%s\' method is not callable for entity \'%s\'', $removeMethodName, $entityName)
            );
        }

        $collection = $this->resolveEntityCollection($object);
        $values = $this->prepareCollectionValues($entityName, $value);

        $toAdd = $this->createArrayCollection(array_udiff($values, $collection, [$this, 'compareEntities']));
        if (!$toAdd->isEmpty()) {
            $object->$addMethodName($toAdd);
        }

        $toRemove = $this->createArrayCollection(array_udiff($collection, $values, [$this, 'compareEntities']));
        if (!$toRemove->isEmpty()) {
            $object->$removeMethodName($toRemove);
        }

        return $this->resolveEntityCollection($object);
    }

    /**
     * @return array<int, EntityInterface>
     *
     * @throws InvalidArgumentException
     */
    private function resolveEntityCollection(object $object): array
    {
        $methodName = 'get' . ucfirst($this->name);
        if (!is_callable([$object, $methodName])) {
            throw new InvalidArgumentException(
                sprintf('The method \'%s\' is not callable for entity \'%s\'', $methodName, get_class($object))
            );
        }

        if (!$this->isInitialized($object)) {
            return [];
        }

        $collection = $object->$methodName();
        if ($collection instanceof Collection) {
            $collection = $collection->toArray();
        }

        return $collection;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function isInitialized(object $object): bool
    {
        try {
            $reflectionProperty = new \ReflectionProperty(get_class($object), $this->name);
        } catch (\Throwable $e) {
            throw new InvalidArgumentException(
                sprintf(
                    'Failed to create reflection property \'%s::%s\': %s',
                    get_class($object),
                    $this->name,
                    $e->getMessage()
                ),
                $e->getCode(),
                $e
            );
        }

        return $reflectionProperty->isInitialized($object);
    }

    /**
     * @param iterable<int, EntityInterface|int|string|array<mixed>> $value
     *
     * @return array<int, EntityInterface>
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    private function prepareCollectionValues(string $entityName, iterable $value): array
    {
        $collection = [];
        foreach ($value as $item) {
            if ($item instanceof EntityInterface) {
                $collection[] = $collection;
                continue;
            }

            if (empty($item)) {
                $collection[] = null;
                continue;
            }

            $id = $this->resolveId($item);

            $entity = empty($id)
                ? $this->createInstance($entityName)
                : $this->getById($entityName, $id);

            $collection[] = is_array($item)
                ? $this->hydrator->hydrate($item, $entity)
                : $entity;
        }

        return array_filter(
            $collection,
            static fn ($item) => (isset($item) && $item instanceof EntityInterface)
        );
    }

    /**
     * @throws RuntimeException
     */
    private function createInstance(string $entityName): object
    {
        if (!class_exists($entityName, true)) {
            throw new RuntimeException(
                sprintf(
                    'The hydrator was unable to create a reflection instance for class \'%s\': %s',
                    'The class could not be found',
                    $entityName,
                )
            );
        }

        try {
            return (new \ReflectionClass($entityName))->newInstanceWithoutConstructor();
        } catch (\ReflectionException $e) {
            throw new RuntimeException(
                sprintf('The reflection class \'%s\' could not be created: %s', $entityName, $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    private function getById(string $entityName, int|string $id): object
    {
        try {
            $entity = $this->repository->find($id);
        } catch (\Exception $e) {
            throw new RuntimeException(
                sprintf(
                    'Collection item of type \'%s\', with id \'%d\' could not be found: %s',
                    $entityName,
                    $id,
                    $e->getMessage()
                ),
                $e->getCode(),
                $e
            );
        }

        if (null === $entity) {
            throw new InvalidArgumentException(
                sprintf(
                    'Collection item of type \'%s\' with id \'%d\' could not be found',
                    $entityName,
                    $id
                )
            );
        }

        return $entity;
    }

    /**
     * @param array<int, EntityInterface> $items
     *
     * @return ArrayCollection<int, EntityInterface>
     */
    private function createArrayCollection(array $items): ArrayCollection
    {
        return new ArrayCollection($items);
    }

    private function compareEntities(EntityInterface $a, EntityInterface $b): int
    {
        return strcmp(spl_object_hash($a), spl_object_hash($b));
    }

    /**
     * @param mixed $value
     *
     * @return iterable<EntityInterface>
     */
    public function extract($value, ?object $object = null): iterable
    {
        return $value;
    }

    public function setObject(?object $object): void
    {
        $this->object = $object;
    }

    public function getObject(): ?object
    {
        return $this->object;
    }
}
