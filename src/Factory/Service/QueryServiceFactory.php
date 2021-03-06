<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Service;

use Arp\DoctrineEntityRepository\Query\QueryService;
use Arp\DoctrineEntityRepository\Query\QueryServiceInterface;
use Arp\LaminasFactory\AbstractFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Psr\Log\NullLogger;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Service
 */
final class QueryServiceFactory extends AbstractFactory
{
    use EntityManagerFactoryProviderTrait;

    /**
     * @noinspection PhpMissingParamTypeInspection
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return QueryServiceInterface
     *
     * @throws ServiceNotCreatedException
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ): QueryServiceInterface {
        $options = $options ?? $this->getServiceOptions($container, $requestedName, 'query_services');

        $className = $options['class_name'] ?? QueryService::class;
        $entityName = $options['entity_name'] ?? $requestedName;

        if (empty($entityName)) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The required \'entity_name\' configuration option is missing for service \'%s\'',
                    $requestedName
                )
            );
        }

        $entityManager = $options['entity_manager'] ?? null;
        if (empty($entityManager)) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The required \'entity_manager\' configuration option is missing for service \'%s\'',
                    $requestedName
                )
            );
        }

        $entityManager = $this->getEntityManager($container, $entityManager, $requestedName);

        return new $className($entityName, $entityManager, new NullLogger());
    }
}
