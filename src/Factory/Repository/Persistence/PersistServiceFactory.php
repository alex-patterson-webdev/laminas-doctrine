<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Repository\Persistence;

use Arp\Entity\EntityInterface;
use Arp\LaminasDoctrine\Factory\Service\EntityManager\EntityManagerFactoryProviderTrait;
use Arp\LaminasDoctrine\Repository\Persistence\PersistService;
use Arp\LaminasDoctrine\Repository\Persistence\PersistServiceInterface;
use Arp\LaminasFactory\AbstractFactory;
use Arp\LaminasMonolog\Factory\FactoryLoggerProviderTrait;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class PersistServiceFactory extends AbstractFactory
{
    use EntityManagerFactoryProviderTrait;
    use FactoryLoggerProviderTrait;

    /**
     * @param ContainerInterface&ServiceLocatorInterface $container
     * @param array<mixed>|null $options
     *
     * @return PersistServiceInterface<EntityInterface>
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array $options = null
    ): PersistServiceInterface {
        $options = array_replace_recursive($this->getServiceOptions($container, $requestedName), $options ?? []);

        $entityManager = $options['entity_manager'] ?? null;
        if (empty($entityManager)) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The required \'entity_manager\' configuration option is missing for service \'%s\'',
                    $requestedName
                )
            );
        }

        return new PersistService(
            $this->getEntityManager($container, $entityManager, $requestedName),
            $this->getLogger($container, $options['logger'] ?? null, $requestedName)
        );
    }
}
