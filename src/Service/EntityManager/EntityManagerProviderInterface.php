<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Service\EntityManager;

use Arp\LaminasDoctrine\Service\EntityManager\Exception\EntityManagerProviderException;
use Doctrine\ORM\EntityManagerInterface;

interface EntityManagerProviderInterface
{
    public function hasEntityManager(string $name): bool;

    /**
     * @throws EntityManagerProviderException
     */
    public function getEntityManager(string $name): EntityManagerInterface;

    /**
     * @param array<string, EntityManagerInterface> $entityManagers
     */
    public function setEntityManagers(array $entityManagers): void;

    public function setEntityManager(string $name, EntityManagerInterface $entityManager): void;
}
