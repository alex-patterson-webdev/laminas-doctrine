<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Repository;

use Arp\Entity\EntityInterface;
use Doctrine\Persistence\ObjectRepository;

interface EntityRepositoryProviderInterface
{
    public function hasRepository(string $entityName): bool;

    /**
     * @param array<string, mixed> $options
     *
     * @return ObjectRepository<EntityInterface>&EntityRepositoryInterface<EntityInterface>
     *
     * @throws \Exception
     */
    public function getRepository(string $entityName, array $options = []): EntityRepositoryInterface;
}
