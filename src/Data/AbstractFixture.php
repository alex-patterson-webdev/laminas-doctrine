<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Data;

use Arp\LaminasDoctrine\Data\Repository\ReferenceRepository;

abstract class AbstractFixture extends \Doctrine\Common\DataFixtures\AbstractFixture
{
    /**
     * @var ReferenceRepository
     */
    protected $referenceRepository;

    public function hasCollectionReference(string $name): bool
    {
        return $this->referenceRepository->hasCollectionReference($name);
    }

    /**
     * @return iterable<mixed>
     *
     * @throws \OutOfBoundsException
     */
    public function getCollectionReference(string $name): iterable
    {
        return $this->referenceRepository->getCollectionReference($name);
    }

    /**
     * @param iterable<mixed> $collection
     */
    public function setCollectionReference(string $name, iterable $collection): void
    {
        $this->referenceRepository->setCollectionReference($name, $collection);
    }

    /**
     * @param iterable<mixed> $collection
     *
     * @throws \BadFunctionCallException
     */
    public function addCollectionReference(string $name, iterable $collection): void
    {
        $this->referenceRepository->addCollectionReference($name, $collection);
    }
}
