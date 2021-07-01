<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Service\EntityManager;

use Arp\LaminasDoctrine\Service\EntityManager\Exception\EntityManagerProviderException;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service that managers a collection of entity managers
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Service\EntityManager
 */
interface EntityManagerProviderInterface
{
    /**
     * Check if the entity manager is available
     *
     * @param string $name The name of the entity manager to check
     *
     * @return bool
     */
    public function hasEntityManager(string $name): bool;

    /**
     * Return a stored entity manager instance matching $name
     *
     * @param string $name
     *
     * @return EntityManagerInterface
     *
     * @throws EntityManagerProviderException
     */
    public function getEntityManager(string $name): EntityManagerInterface;

    /**
     * Set a collection of entity managers
     *
     * @param array<string, EntityManagerInterface> $entityManagers
     */
    public function setEntityManagers(array $entityManagers): void;

    /**
     * Set the entity manager instance with the provided $name
     *
     * @param string                 $name
     * @param EntityManagerInterface $entityManager
     */
    public function setEntityManager(string $name, EntityManagerInterface $entityManager): void;
}
