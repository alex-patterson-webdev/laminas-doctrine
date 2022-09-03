<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Repository;

use Arp\Entity\EntityInterface;

interface EntityRepositoryProviderInterface
{
    public function hasRepository(string $entityName): bool;

    /**
     * @param string               $entityName
     * @param array<string, mixed> $options
     *
     * @return EntityRepositoryInterface<EntityInterface>
     *
     * @throws \Exception
     */
    public function getRepository(string $entityName, array $options = []): EntityRepositoryInterface;
}
