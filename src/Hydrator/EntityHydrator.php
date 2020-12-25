<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Hydrator;

use Doctrine\Laminas\Hydrator\DoctrineObject;
use Doctrine\Laminas\Hydrator\Strategy\AbstractCollectionStrategy;
use Laminas\Hydrator\Exception\InvalidArgumentException;
use Laminas\Hydrator\Exception\RuntimeException;
use Laminas\Hydrator\Filter\FilterProviderInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Hydrator
 */
final class EntityHydrator extends DoctrineObject
{
    /**
     * @var \ReflectionClass|null
     */
    private ?\ReflectionClass $reflectionClass = null;

    /**
     * @noinspection PhpMissingParamTypeInspection
     * @noinspection ReturnTypeCanBeDeclaredInspection
     *
     * @param object $object
     * @param mixed  $collectionName
     * @param string $target
     * @param mixed  $values
     *
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws \InvalidArgumentException
     */
    protected function toMany($object, $collectionName, $target, $values)
    {
        $metadata = $this->objectManager->getClassMetadata(ltrim($target, '\\'));
        $identifier = $metadata->getIdentifier();

        if (! is_array($values) && ! $values instanceof \Traversable) {
            $values = (array) $values;
        }

        $collection = [];

        // If the collection contains identifiers, fetch the objects from database
        foreach ($values as $value) {
            if ($value instanceof $target) {
                // assumes modifications have already taken place in object
                $collection[] = $value;
                continue;
            }

            if (empty($value)) {
                // assumes no id and retrieves new $target
                $collection[] = $this->find($value, $target);
                continue;
            }

            $find = [];
            if (is_array($identifier)) {
                foreach ($identifier as $field) {
                    switch (gettype($value)) {
                        case 'object':
                            $getter = 'get' . ucfirst($field);

                            if (is_callable([$value, $getter])) {
                                $find[$field] = $value->$getter();
                            } elseif (property_exists($value, $field)) {
                                $find[$field] = $value->$field;
                            }
                            break;
                        case 'array':
                            if (array_key_exists($field, $value) && $value[$field] !== null) {
                                $find[$field] = $value[$field];
                                unset($value[$field]); // removed identifier from persistable data
                            }
                            break;
                        default:
                            $find[$field] = $value;
                            break;
                    }
                }
            }

            if (! empty($find) && $found = $this->find($find, $target)) {
                $collection[] = (is_array($value)) ? $this->hydrate($value, $found) : $found;
            } else {
                $newTarget = $this->createTargetEntity($target);

                $collection[] = (is_array($value)) ? $this->hydrate($value, $newTarget) : $newTarget;
            }
        }

        $collection = array_filter(
            $collection,
            static fn($item) => null !== $item
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
     */
    private function createTargetEntity(string $className): object
    {
        return $this->getReflectionClass($className)->newInstanceWithoutConstructor();
    }

    /**
     * Copied from parent to check for isInitialisedFieldName()
     *
     * @noinspection PhpMissingParamTypeInspection
     *
     * @param object $object
     *
     * @return array
     *
     * @throws RuntimeException
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
                0 === strpos($fieldName, 'is')
                && in_array($fieldName, $methods, true)
                && ctype_upper(substr($fieldName, 2, 1))
            ) {
                $data[$dataFieldName] = $this->extractValue($fieldName, $object->$fieldName(), $object);
            }
        }

        return $data;
    }

    /**
     * Check if the provided $fieldName is initialised for the given $object
     *
     * @param object $object
     * @param string $fieldName
     *
     * @return bool
     *
     * @throws RuntimeException
     */
    protected function isInitialisedFieldName(object $object, string $fieldName): bool
    {
        $property = $this->getReflectionProperty($object, $fieldName);

        $isPublic = $property->isPublic();
        if (!$isPublic) {
            $property->setAccessible(true);
        }

        $initialized = $property->isInitialized($object);

        if (!$isPublic) {
            $property->setAccessible(false);
        }

        return $initialized;
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
     * @return \ReflectionClass
     *
     * @throws RuntimeException
     */
    private function getReflectionClass(string $className): \ReflectionClass
    {
        if (null !== $this->reflectionClass && $this->reflectionClass->getName() === $className) {
            return $this->reflectionClass;
        }

        try {
            $this->reflectionClass = new \ReflectionClass($className);
        } catch (\Throwable $e) {
            throw new RuntimeException(
                sprintf(
                    'The hydrator was unable to create a reflection instance for class \'%s\': %s',
                    $className,
                    $e->getMessage()
                ),
                $e->getCode(),
                $e
            );
        }

        return $this->reflectionClass;
    }
}
