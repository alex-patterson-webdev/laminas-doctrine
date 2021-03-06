<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Service;

use Arp\LaminasDoctrine\Service\EntityManagerProvider;
use Arp\LaminasDoctrine\Service\EntityManagerProviderInterface;
use Arp\LaminasDoctrine\Service\Exception\EntityManagerProviderException;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Psr\Container\ContainerInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Service
 */
trait EntityManagerFactoryProviderTrait
{
    /**
     * @param ContainerInterface            $container
     * @param string|EntityManagerInterface $name
     * @param string                        $serviceName
     *
     * @return EntityManagerInterface
     *
     * @throws ServiceNotCreatedException
     */
    protected function getEntityManager(
        ContainerInterface $container,
        $name,
        string $serviceName
    ): EntityManagerInterface {
        $entityManager = $name;

        if (is_string($name)) {
            /** @var EntityManagerProviderInterface $entityManagerProvider */
            $entityManagerProvider = $container->get(EntityManagerProvider::class);

            try {
                $entityManager = $entityManagerProvider->getEntityManager($name);
            } catch (EntityManagerProviderException $e) {
                throw new ServiceNotCreatedException(
                    sprintf(
                        'The entity manager \'%s\' could not be found for service \'%s\'',
                        $name,
                        $serviceName
                    ),
                    $e->getCode(),
                    $e
                );
            }
        }

        if (!$entityManager instanceof EntityManagerInterface) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The entity manager must be an object of type \'%s\'; \'%s\' provided for service \'%s\'',
                    EntityManagerInterface::class,
                    (is_object($entityManager) ? get_class($entityManager) : gettype($entityManager)),
                    $serviceName
                )
            );
        }

        return $entityManager;
    }
}
