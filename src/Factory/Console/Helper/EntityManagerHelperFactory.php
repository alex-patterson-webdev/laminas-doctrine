<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Console\Helper;

use Arp\LaminasDoctrine\Factory\Service\EntityManager\EntityManagerFactoryProviderTrait;
use Arp\LaminasDoctrine\Factory\Service\EntityManager\ObjectManagerArgvInputProviderTrait;
use Arp\LaminasFactory\AbstractFactory;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class EntityManagerHelperFactory extends AbstractFactory
{
    use ObjectManagerArgvInputProviderTrait;
    use EntityManagerFactoryProviderTrait;

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array<string, mixed>|null $options
     *
     * @return EntityManagerHelper
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
    ): EntityManagerHelper {
        $options = $options ?? $this->getServiceOptions($container, $requestedName);

        // Attempt to fetch the name of the entity manager from command line arguments
        if (empty($options['entity_manager'])) {
            $options['entity_manager'] = $this->getEntityManagerArgvInput();
        }

        if (!empty($options['default_object_manager'])) {
            $options['entity_manager'] = $options['default_object_manager'];
        }

        if (empty($options['entity_manager'])) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The required \'entity_manager\' configuration option is missing for service \'%s\'',
                    $requestedName
                )
            );
        }

        return new EntityManagerHelper(
            $this->getEntityManager($container, $options['entity_manager'], $requestedName)
        );
    }
}
