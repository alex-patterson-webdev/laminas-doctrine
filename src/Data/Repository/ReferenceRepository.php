<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Data\Repository;

/**
 * Extended reference repository (outside of an available interface) we add the ability to set a collection
 * of referenced entities
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Data\Repository
 */
class ReferenceRepository extends \Doctrine\Common\DataFixtures\ReferenceRepository
{
    /**
     * @var iterable[]
     */
    private array $collectionReferences = [];

    /**
     * @param string $name
     *
     * @return iterable
     *
     * @throws \OutOfBoundsException
     */
    public function getCollectionReference(string $name): iterable
    {
        if (!$this->hasCollectionReference($name)) {
            throw new \OutOfBoundsException(sprintf('Collection reference to "%s" does not exist', $name));
        }

        $collection = [];
        foreach ($this->collectionReferences[$name] as $index => $reference) {
            $collection[$index] = $this->getReference($reference);
        }

        return $collection;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasCollectionReference(string $name): bool
    {
        return array_key_exists($name, $this->collectionReferences);
    }

    /**
     * @param string   $name
     * @param iterable $collection
     *
     * @throws \BadFunctionCallException
     */
    public function addCollectionReference(string $name, iterable $collection): void
    {
        if ($this->hasCollectionReference($name)) {
            throw new \BadMethodCallException(
                sprintf(
                    'Reference to "%s" already exists, use method setCollectionReference in order to override it',
                    $name
                )
            );
        }
        $this->setCollectionReference($name, $collection);
    }

    /**
     * @param string   $name
     * @param iterable $collection
     */
    public function setCollectionReference(string $name, iterable $collection): void
    {
        foreach ($collection as $index => $item) {
            $itemName = $name . '.' . $index;
            $this->setReference($itemName, $item);
            $this->collectionReferences[$name][$index] = $itemName;
        }
    }
}
