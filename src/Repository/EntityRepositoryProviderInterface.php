<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Repository;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine
 */
interface EntityRepositoryProviderInterface
{
    /**
     * @param string $entityName
     *
     * @return bool
     */
    public function hasRepository(string $entityName): bool;

    /**
     * @param string               $entityName
     * @param array<string, mixed> $options
     *
     * @return EntityRepositoryInterface
     *
     * @throws \Throwable
     */
    public function getRepository(string $entityName, array $options = []): EntityRepositoryInterface;
}
