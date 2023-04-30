<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\DataFixture;

use Arp\LaminasDoctrine\Factory\Service\EntityManager\EntityManagerFactoryProviderTrait;
use Arp\LaminasDoctrine\Factory\Service\EntityManager\ObjectManagerArgvInputProviderTrait;
use Arp\LaminasFactory\AbstractFactory;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class OrmPurgerFactory extends AbstractFactory
{
    use ObjectManagerArgvInputProviderTrait;
    use EntityManagerFactoryProviderTrait;

    /**
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, string $requestedName, array $options = null): ORMPurger
    {
        $options = $options ?? $this->getServiceOptions($container, $requestedName);

        $entityManagerName = $options['entity_manager'] ?? null;
        if (null === $entityManagerName) {
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

        $purger = new ORMPurger(
            $this->getEntityManager($container, $entityManagerName, $requestedName),
            $options['excluded_table_names'] ?? []
        );

        $purger->setPurgeMode($options['mode'] ?? ORMPurger::PURGE_MODE_DELETE);

        return $purger;
    }
}
