<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\DataFixture;

use Arp\LaminasDoctrine\Data\Repository\ReferenceRepository;
use Arp\LaminasDoctrine\Factory\Service\EntityManagerFactoryProviderTrait;
use Arp\LaminasDoctrine\Factory\Service\ObjectManagerArgvInputProviderTrait;
use Arp\LaminasFactory\AbstractFactory;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Purger\PurgerInterface;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrineFixtures\Factory\Data
 */
final class OrmExecutorFactory extends AbstractFactory
{
    use ObjectManagerArgvInputProviderTrait;
    use EntityManagerFactoryProviderTrait;

    /**
     * @noinspection PhpMissingParamTypeInspection
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return ORMExecutor
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ORMExecutor
    {
        $options = $options ?? $this->getServiceOptions($container, $requestedName);

        $entityManagerName = $options['entity_manager'] ?? null;
        if (empty($entityManagerName)) {
            $entityManagerName = $this->getEntityManagerArgvInput();
        }

        if (empty($entityManagerName)) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The \'entity_manager\' configuration option could not be found for service \'%s\'',
                    $requestedName
                )
            );
        }

        $entityManager = $this->getEntityManager($container, $entityManagerName, $requestedName);

        $executor = new ORMExecutor(
            $entityManager,
            $this->getPurger($container, $options['purger'] ?? null, $requestedName),
        );

        $executor->setReferenceRepository(new ReferenceRepository($entityManager));

        return $executor;
    }

    /**
     * @param ContainerInterface    $container
     * @param ORMPurger|string|null $purger
     * @param string                $serviceName
     *
     * @return ORMPurger|null
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     */
    private function getPurger(ContainerInterface $container, $purger, string $serviceName): ?ORMPurger
    {
        if (null === $purger) {
            return null;
        }

        if (is_string($purger)) {
            $purger = $this->getService($container, $purger, $serviceName);
        }

        if (!$purger instanceof PurgerInterface) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The \'purger\' must be an object of type \'%s\'; \'%s\' provided for service \'%s\'',
                    PurgerInterface::class,
                    is_object($purger) ? get_class($purger) : gettype($purger),
                    $serviceName
                )
            );
        }

        return $purger;
    }
}
