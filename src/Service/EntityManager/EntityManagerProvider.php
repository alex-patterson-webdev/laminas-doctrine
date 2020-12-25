<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Service\EntityManager;

use Arp\LaminasDoctrine\Config\DoctrineConfig;
use Arp\LaminasDoctrine\Factory\Service\EntityManagerFactory;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Service\EntityManager
 */
final class EntityManagerProvider implements EntityManagerProviderInterface
{
    /**
     * @var DoctrineConfig
     */
    private DoctrineConfig $config;

    /**
     * @var EntityManagerManager
     */
    private EntityManagerManager $manager;

    /**
     * @param DoctrineConfig       $config
     * @param EntityManagerManager $manager
     * @param array                $entityManagers
     */
    public function __construct(DoctrineConfig $config, EntityManagerManager $manager, array $entityManagers = [])
    {
        $this->config = $config;
        $this->manager = $manager;

        $this->setEntityManagers($entityManagers);
    }

    /**
     * Set the configuration options for a single entity manager with the provided $name
     *
     * @param string $name
     * @param array  $config
     */
    public function setEntityManagerConfig(string $name, array $config): void
    {
        $this->config->setEntityManagerConfig($name, $config);
    }

    /**
     * Check if the entity manager is registered with the provider
     *
     * @param string $name The name of the entity manager to check
     *
     * @return bool
     */
    public function hasEntityManager(string $name): bool
    {
        return $this->manager->has($name) || $this->config->hasEntityManagerConfig($name);
    }

    /**
     * @param string $name
     *
     * @return EntityManagerInterface
     *
     * @throws EntityManagerProviderException
     */
    public function getEntityManager(string $name): EntityManagerInterface
    {
        try {
            if (!$this->manager->has($name) && $this->config->hasEntityManagerConfig($name)) {
                $this->manager->setService($name, $this->create($name, $this->config->getEntityManagerConfig($name)));
            }

            if ($this->manager->has($name)) {
                return $this->manager->get($name);
            }
        } catch (EntityManagerProviderException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new EntityManagerProviderException(
                sprintf('Failed return entity manager \'%s\': %s', $name, $e->getMessage()),
                $e->getCode(),
                $e
            );
        }

        throw new EntityManagerProviderException(
            sprintf('Unable to find entity manager \'%s\'', $name)
        );
    }

    /**
     * @param string $name
     *
     * @return EntityManagerInterface
     *
     * @throws EntityManagerProviderException
     */
    public function refresh(string $name): EntityManagerInterface
    {
        if ($this->manager->has($name)) {
            $entityManager = $this->getEntityManager($name);

            if ($entityManager->isOpen()) {
                $entityManager->close();
            }

            $entityManager = $this->create($name, $this->config->getEntityManagerConfig($name));
            $this->manager->setService($name, $entityManager);
        }

        return $this->getEntityManager($name);
    }

    /**
     * @param string                 $name
     * @param EntityManagerInterface $entityManager
     */
    public function setEntityManager(string $name, EntityManagerInterface $entityManager): void
    {
        $this->manager->setService($name, $entityManager);
    }

    /**
     * @param array $entityManagers
     */
    public function setEntityManagers(array $entityManagers): void
    {
        foreach ($entityManagers as $name => $entityManager) {
            $this->setEntityManager($name, $entityManager);
        }
    }

    /**
     * @param string      $name
     * @param array       $config
     * @param string|null $factoryClassName
     *
     * @return EntityManagerInterface
     *
     * @throws EntityManagerProviderException
     */
    private function create(string $name, array $config, string $factoryClassName = null): EntityManagerInterface
    {
        if (!$this->manager->has($name)) {
            // There is no manual entry for this entity manager. We can manually add it so we do not need
            // to explicitly define it each time with the 'entity_manager_manager'
            $this->manager->setFactory($name, $factoryClassName ?? EntityManagerFactory::class);
        }

        try {
            return $this->manager->build($name, $config);
        } catch (\Throwable $e) {
            throw new EntityManagerProviderException(
                sprintf('Failed to create entity manager \'%s\' from configuration: %s', $name, $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }
}
