<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\DataFixture;

use Arp\LaminasDoctrine\Data\Repository\ReferenceRepository;
use Arp\LaminasDoctrine\Factory\Service\EntityManager\EntityManagerFactoryProviderTrait;
use Arp\LaminasDoctrine\Factory\Service\EntityManager\ObjectManagerArgvInputProviderTrait;
use Arp\LaminasFactory\AbstractFactory;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\Purger\PurgerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class OrmExecutorFactory extends AbstractFactory
{
    use ObjectManagerArgvInputProviderTrait;
    use EntityManagerFactoryProviderTrait;

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array<string, mixed>|null $options
     *
     * @return ORMExecutor
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, string $requestedName, array $options = null): ORMExecutor
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
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     */
    private function getPurger(
        ContainerInterface $container,
        ORMPurger|string|null $purger,
        string $serviceName
    ): ?ORMPurger {
        if (null === $purger) {
            return null;
        }

        if (is_string($purger)) {
            $purger = $this->getService($container, $purger, $serviceName);
        }

        if (!$purger instanceof ORMPurger) {
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
