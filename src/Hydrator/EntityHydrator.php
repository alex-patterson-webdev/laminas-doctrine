<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Hydrator;

use Doctrine\Laminas\Hydrator\DoctrineObject;
use Doctrine\Laminas\Hydrator\Strategy\AbstractCollectionStrategy;
use Laminas\Hydrator\Exception\InvalidArgumentException;
use Laminas\Hydrator\Exception\RuntimeException;
use Laminas\Hydrator\Filter\FilterProviderInterface;

/**
 * When using hydrators and PHP 7.4+ type hinted properties, there will be times where our entity classes will be
 * instantiated via reflection (due to the Doctrine/Laminas hydration processes). This instantiation will bypass the
 * entity's __construct() and therefore not initialise the default class property values. This will lead to
 * "Typed property must not be accessed before initialization" fatal errors, despite using the hydrators in their
 * intended way. This class extends the existing DoctrineObject in order to first check if the requested property has
 * been instantiated before attempting to use it as part of the extractByValue() method.
 *
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
                // Assumes modifications have already taken place in object
                $collection[] = $value;
                continue;
            }

            if (empty($value)) {
                // Assumes no id and retrieves new $target
                $collection[] = $this->find($value, $target);
                continue;
            }

            $find = is_array($identifier) ? $this->getFindCriteria($identifier, $value) : [];

            if (!empty($find) && $found = $this->find($find, $target)) {
                $collection[] = is_array($value) ? $this->hydrate($value, $found) : $found;
                continue;
            }

            $newTarget = $this->createTargetEntity($target);
            $collection[] = is_array($value) ? $this->hydrate($value, $newTarget) : $newTarget;
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
        return $this->createReflectionClass($className)->newInstanceWithoutConstructor();
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
        $this->reflectionClass = $this->createReflectionClass($className);
        return $this->reflectionClass;
    }

    /**
     * @param array $identifier
     * @param mixed  $value
     *
     * @return array
     */
    protected function getFindCriteria(array $identifier, $value): array
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
     * @return \ReflectionClass
     */
    private function createReflectionClass(string $className): \ReflectionClass
    {
        try {
            return new \ReflectionClass($className);
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
    }
}
