<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Console\Helper;

use Arp\LaminasDoctrine\Factory\Service\EntityManagerFactoryProviderTrait;
use Arp\LaminasDoctrine\Factory\Service\ObjectManagerArgvInputProviderTrait;
use Arp\LaminasFactory\AbstractFactory;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Console\Helper
 */
final class EntityManagerHelperFactory extends AbstractFactory
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
     * @return EntityManagerHelper
     *
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): EntityManagerHelper
    {
        $options = $options ?? $this->getServiceOptions($container, $requestedName);

        // Attempt to fetch the name of the entity manager from command line arguments
        if (empty($options['entity_manager'])) {
            $options['entity_manager'] = $this->getEntityManagerArgvInput();
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
