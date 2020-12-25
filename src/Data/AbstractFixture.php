<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Data;

use Arp\LaminasDoctrine\Data\Repository\ReferenceRepository;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Data
 */
abstract class AbstractFixture extends \Doctrine\Common\DataFixtures\AbstractFixture
{
    /**
     * @var ReferenceRepository
     */
    protected $referenceRepository;

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasCollectionReference(string $name): bool
    {
        return $this->referenceRepository->hasCollectionReference($name);
    }

    /**
     * @param string $name
     *
     * @return iterable
     *
     * @throws \OutOfBoundsException
     */
    public function getCollectionReference(string $name): iterable
    {
        return $this->referenceRepository->getCollectionReference($name);
    }

    /**
     * @param string   $name
     * @param iterable $collection
     */
    public function setCollectionReference(string $name, iterable $collection): void
    {
        $this->referenceRepository->setCollectionReference($name, $collection);
    }

    /**
     * @param string   $name
     * @param iterable $collection
     *
     * @throws \BadFunctionCallException
     */
    public function addCollectionReference(string $name, iterable $collection): void
    {
        $this->referenceRepository->addCollectionReference($name, $collection);
    }
}
