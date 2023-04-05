<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Repository;

interface EntityRepositoryProviderInterface
{
    public function hasRepository(string $entityName): bool;

    /**
     * @param array<string, mixed> $options
     *
     * @throws \Exception
     */
    public function getRepository(string $entityName, array $options = []): EntityRepositoryInterface;
}
