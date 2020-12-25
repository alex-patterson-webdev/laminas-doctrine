<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\DataFixture;

use Arp\LaminasDoctrine\Factory\Service\EntityManagerFactoryProviderTrait;
use Arp\LaminasDoctrine\Factory\Service\ObjectManagerArgvInputProviderTrait;
use Arp\LaminasFactory\AbstractFactory;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Data
 */
final class OrmPurgerFactory extends AbstractFactory
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
     * @return ORMPurger
     *
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ORMPurger
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
