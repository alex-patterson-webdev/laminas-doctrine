<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Service;

use Arp\LaminasDoctrine\Service\EntityManager\EntityManagerManager;
use Arp\LaminasFactory\AbstractFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\InvalidArgumentException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Service
 */
final class EntityManagerManagerFactory extends AbstractFactory
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return EntityManagerManager
     *
     * @noinspection PhpMissingParamTypeInspection
     *
     * @throws InvalidArgumentException
     * @throws ServiceNotFoundException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): EntityManagerManager
    {
        $config = $this->getApplicationOptions($container, 'entity_manager_manager') ?: [];

        return new EntityManagerManager($container, $config);
    }
}
