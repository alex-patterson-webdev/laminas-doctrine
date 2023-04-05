<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Hydrator;

use Doctrine\Laminas\Hydrator\DoctrineObject;
use Doctrine\Laminas\Hydrator\Strategy\AbstractCollectionStrategy;
use Doctrine\ORM\Proxy\Proxy;
use Laminas\Hydrator\Exception\InvalidArgumentException;
use Laminas\Hydrator\Exception\RuntimeException;
use Laminas\Hydrator\Filter\FilterProviderInterface;

/**
 * When using hydrators and PHP 7.4+ type hinted properties, there will be times when our entity classes will be
 * instantiated via reflection (due to the Doctrine/Laminas hydration processes). This instantiation will bypass the
 * entity's __construct() and therefore not initialise the default class property values. This will lead to
 * "Typed property must not be accessed before initialization" fatal errors, despite using the hydrators in their
 * intended way. This class extends the existing DoctrineObject in order to first check if the requested property has
 * been instantiated before attempting to use it as part of the extractByValue() method.
 */
final class EntityHydrator extends DoctrineObject
{
    /**
     * @var \ReflectionClass<object>|null
     */
    private ?\ReflectionClass $reflectionClass = null;

    /**
     * @param object               $object
     * @param mixed                $collectionName
     * @param class-string<object> $target
     * @param array<mixed>|null    $values
     *
     * @return void
     *
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws \InvalidArgumentException
     * @throws \ReflectionException
     */
    protected function toMany($object, $collectionName, $target, $values): void
    {
        if (!is_iterable($values)) {
            $values = (array)$values;
        }

        $metadata = $this->objectManager->getClassMetadata($target);
        $identifier = $metadata->getIdentifier();
        $collection = [];

        // If the collection contains identifiers, fetch the objects from database
        foreach ($values as $value) {
            if ($value instanceof $target) {
                // Assumes modifications have already taken place in object
                $collection[] = $value;
                continue;
            }

            if (empty($value)) {
                // Assumes no id and retrieves new $target
                $collection[] = $this->find($value, $target);
                continue;
            }

            $find = $this->getFindCriteria($identifier, $value);

            if (!empty($find) && $found = $this->find($find, $target)) {
                $collection[] = is_array($value) ? $this->hydrate($value, $found) : $found;
                continue;
            }

            $newTarget = $this->createTargetEntity($target);
            $collection[] = is_array($value) ? $this->hydrate($value, $newTarget) : $newTarget;
        }

        $collection = array_filter(
            $collection,
            static fn ($item) => null !== $item
        );

        /** @var AbstractCollectionStrategy $collectionStrategy */
        $collectionStrategy = $this->getStrategy($collectionName);
        $collectionStrategy->setObject($object);

        $this->hydrateValue($collectionName, $collection, $values);
    }

    /**
     * @param string $className
     *
     * @return object
     *
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    private function createTargetEntity(string $className): object
    {
        return $this->createReflectionClass($className)->newInstanceWithoutConstructor();
    }

    /**
     * Copied from parent to check for isInitialisedFieldName()
     *
     * @param object $object
     *
     * @return array<string, mixed>
     *
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function extractByValue($object): array
    {
        $fieldNames = array_merge($this->metadata->getFieldNames(), $this->metadata->getAssociationNames());
        $methods = get_class_methods($object);
        $filter = $object instanceof FilterProviderInterface
            ? $object->getFilter()
            : $this->filterComposite;

        $data = [];
        foreach ($fieldNames as $fieldName) {
            if (!$this->isInitialisedFieldName($object, $fieldName)) {
                continue;
            }
            if ($filter && !$filter->filter($fieldName)) {
                continue;
            }

            $getter = 'get' . ucfirst($fieldName);
            $isser = 'is' . ucfirst($fieldName);

            $dataFieldName = $this->computeExtractFieldName($fieldName);
            if (in_array($getter, $methods, true)) {
                $data[$dataFieldName] = $this->extractValue($fieldName, $object->$getter(), $object);
            } elseif (in_array($isser, $methods, true)) {
                $data[$dataFieldName] = $this->extractValue($fieldName, $object->$isser(), $object);
            } elseif (
                str_starts_with($fieldName, 'is')
                && in_array($fieldName, $methods, true)
                && ctype_upper($fieldName[2])
            ) {
                $data[$dataFieldName] = $this->extractValue($fieldName, $object->$fieldName(), $object);
            }
        }

        return $data;
    }

    /**
     * Check if the provided $fieldName is initialised for the given $object
     *
     * @throws RuntimeException
     */
    protected function isInitialisedFieldName(object $object, string $fieldName): bool
    {
        if ($object instanceof Proxy) {
            return true;
        }
        return $this->getReflectionProperty($object, $fieldName)->isInitialized($object);
    }

    /**
     * @param object $object
     * @param string $fieldName
     *
     * @return \ReflectionProperty
     *
     * @throws RuntimeException
     */
    private function getReflectionProperty(object $object, string $fieldName): \ReflectionProperty
    {
        $className = get_class($object);
        $reflectionClass = $this->getReflectionClass($className);

        if (!$reflectionClass->hasProperty($fieldName)) {
            throw new RuntimeException(
                sprintf(
                    'The hydration property \'%s\' could not be found for class \'%s\'',
                    $fieldName,
                    $className
                )
            );
        }

        try {
            $property = $reflectionClass->getProperty($fieldName);
        } catch (\Throwable $e) {
            throw new RuntimeException(
                sprintf(
                    'The hydration property \'%s\' could not be loaded for class \'%s\': %s',
                    $fieldName,
                    $className,
                    $e->getMessage()
                ),
                $e->getCode(),
                $e
            );
        }

        return $property;
    }

    /**
     * @param string $className
     *
     * @return \ReflectionClass<object>
     *
     * @throws RuntimeException
     */
    private function getReflectionClass(string $className): \ReflectionClass
    {
        if (null !== $this->reflectionClass && $this->reflectionClass->getName() === $className) {
            return $this->reflectionClass;
        }
        $this->reflectionClass = $this->createReflectionClass($className);
        return $this->reflectionClass;
    }

    /**
     * @param array<string|object|array|mixed> $identifier
     * @param mixed                            $value
     *
     * @return array<string|int, mixed>
     */
    protected function getFindCriteria(array $identifier, mixed $value): array
    {
        $find = [];
        foreach ($identifier as $field) {
            if (is_object($value)) {
                $getter = 'get' . ucfirst($field);

                if (is_callable([$value, $getter])) {
                    $find[$field] = $value->$getter();
                } elseif (property_exists($value, $field)) {
                    $find[$field] = $value->{$field};
                }
                continue;
            }

            if (is_array($value)) {
                if (isset($value[$field])) {
                    $find[$field] = $value[$field];
                    unset($value[$field]);
                }
                continue;
            }

            $find[$field] = $value;
        }

        return $find;
    }

    /**
     * @param string $className
     *
     * @return \ReflectionClass<object>
     *
     * @throws RuntimeException
     */
    private function createReflectionClass(string $className): \ReflectionClass
    {
        if (!class_exists($className)) {
            throw new RuntimeException(
                sprintf(
                    'The hydrator was unable to create a reflection instance for class \'%s\': %s',
                    'The class could not be found',
                    $className,
                )
            );
        }

        return new \ReflectionClass($className);
    }

    /**
     * @param mixed $value
     * @param string $typeOfField
     */
    protected function handleTypeConversions(mixed $value, $typeOfField): mixed
    {
        if ($typeOfField === 'string' && $value instanceof \BackedEnum) {
            return $value;
        }

        if ($value !== null && $typeOfField === 'bigint') {
            return (int)$value;
        }

        return parent::handleTypeConversions($value, $typeOfField);
    }
}
