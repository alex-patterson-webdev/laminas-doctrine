<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Hydrator\Strategy;

use Arp\DoctrineEntityRepository\EntityRepositoryInterface;
use Arp\Entity\EntityInterface;
use Arp\LaminasDoctrine\Hydrator\Strategy\Exception\RuntimeException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Laminas\Hydrator\HydratorInterface;
use Laminas\Hydrator\Strategy\Exception\InvalidArgumentException;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Hydrator\Strategy
 */
class HydratorCollectionStrategy extends AbstractHydratorStrategy implements HydrationObjectAwareInterface
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var HydratorInterface
     */
    private HydratorInterface $hydrator;

    /**
     * @var object|EntityInterface|null
     */
    private ?object $object;

    /**
     * @param string                    $name
     * @param EntityRepositoryInterface $repository
     * @param HydratorInterface         $hydrator
     */
    public function __construct(string $name, EntityRepositoryInterface $repository, HydratorInterface $hydrator)
    {
        parent::__construct($repository);

        $this->name = $name;
        $this->hydrator = $hydrator;
    }

    /**
     * @param mixed                     $value
     * @param array<string, mixed>|null $data
     *
     * @return iterable|EntityInterface[]
     *
     * @throws InvalidArgumentException
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
     * @param object $object
     *
     * @return EntityInterface[]
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
     * @param object $object
     *
     * @return bool
     *
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

        $isPublic = $reflectionProperty->isPublic();
        if (!$isPublic) {
            $reflectionProperty->setAccessible(true);
        }

        $isInitialized = $reflectionProperty->isInitialized($object);

        if (!$isPublic) {
            $reflectionProperty->setAccessible(false);
        }

        return $isInitialized;
    }

    /**
     * @param string $entityName
     * @param mixed  $value
     *
     * @return array<EntityInterface>
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    private function prepareCollectionValues(string $entityName, $value): array
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

            // Attempt to resolve the identity of the item
            $id = $this->resolveId($item);

            $entity = empty($id)
                ? $this->createInstance($entityName)
                : $this->getById($entityName, $id);

            $collection[] = is_array($item)
                ? $this->hydrator->hydrate($item, $entity)
                : $entity;
        }

        return array_filter($collection, static fn($item) => null !== $item);
    }

    /**
     * @param string $entityName
     *
     * @return object
     *
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
     * @param string $entityName
     * @param mixed  $id
     *
     * @return object
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    private function getById(string $entityName, $id): object
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
     * @param EntityInterface[] $items
     *
     * @return ArrayCollection<int, EntityInterface>
     */
    private function createArrayCollection(array $items): ArrayCollection
    {
        return new ArrayCollection($items);
    }

    /**
     * @noinspection PhpUnusedPrivateMethodInspection It is used as a user defined callback in array_udiff()
     *
     * @param EntityInterface $a
     * @param EntityInterface $b
     *
     * @return int
     */
    private function compareEntities(EntityInterface $a, EntityInterface $b): int
    {
        return strcmp(spl_object_hash($a), spl_object_hash($b));
    }

    /**
     * @param mixed       $value
     * @param object|null $object
     *
     * @return iterable<EntityInterface>
     */
    public function extract($value, ?object $object = null): iterable
    {
        return $value;
    }

    /**
     * @param object|null $object
     */
    public function setObject(?object $object): void
    {
        $this->object = $object;
    }

    /**
     * @return object|null
     */
    public function getObject(): ?object
    {
        return $this->object;
    }
}
