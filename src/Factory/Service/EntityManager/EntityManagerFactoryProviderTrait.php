<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Service\EntityManager;

use Arp\LaminasDoctrine\Service\EntityManager\EntityManagerProvider;
use Arp\LaminasDoctrine\Service\EntityManager\EntityManagerProviderInterface;
use Arp\LaminasDoctrine\Service\EntityManager\Exception\EntityManagerProviderException;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

trait EntityManagerFactoryProviderTrait
{
    /**
     * @param string|EntityManagerInterface|mixed $name
     *
     * @throws ServiceNotCreatedException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getEntityManager(
        ContainerInterface $container,
        mixed $name,
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
                    is_object($entityManager) ? get_class($entityManager) : gettype($entityManager),
                    $serviceName
                )
            );
        }

        return $entityManager;
    }
}
