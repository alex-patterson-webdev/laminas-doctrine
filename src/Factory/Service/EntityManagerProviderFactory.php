<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Service;

use Arp\LaminasDoctrine\Config\DoctrineConfig;
use Arp\LaminasDoctrine\Service\EntityManagerManager;
use Arp\LaminasDoctrine\Service\EntityManagerProvider;
use Arp\LaminasDoctrine\Service\EntityManagerProviderInterface;
use Arp\LaminasFactory\AbstractFactory;
use Doctrine\ORM\EntityManagerInterface;
use Interop\Container\ContainerInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Service
 */
final class EntityManagerProviderFactory extends AbstractFactory
{
    /**
     * @noinspection PhpMissingParamTypeInspection
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return EntityManagerProviderInterface
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ): EntityManagerProviderInterface {
        /** @var DoctrineConfig $doctrineConfig */
        $doctrineConfig = $this->getService($container, DoctrineConfig::class, $requestedName);

        /** @var EntityManagerManager $entityManagerManager */
        $entityManagerManager = $this->getService($container, EntityManagerManager::class, $requestedName);

        /** @var EntityManagerInterface[] $entityManagers */
        $entityManagers = [];

        return new EntityManagerProvider($doctrineConfig, $entityManagerManager, $entityManagers);
    }
}
