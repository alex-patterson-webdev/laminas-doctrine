<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Repository\Query;

use Arp\DoctrineEntityRepository\Query\QueryService;
use Arp\DoctrineEntityRepository\Query\QueryServiceInterface;
use Arp\LaminasDoctrine\Query\QueryFilterManager;
use Arp\LaminasDoctrine\Repository\Query\QueryFilterService;
use Arp\LaminasFactory\AbstractFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\ServiceManager;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Repository\Query
 */
final class QueryFilterServiceFactory extends AbstractFactory
{
    /**
     * @noinspection PhpMissingParamTypeInspection
     *
     * @param ContainerInterface|ServiceManager $container
     * @param string                            $requestedName
     * @param array|null                        $options
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

        $entityName = $options['entity_name'] ?? null;
        if (null === $entityName) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The required \'entity_name\' configuration option is missing for service \'%s\'',
                    $requestedName
                )
            );
        }

        $className = $options['class_name'] ?? QueryFilterService::class;
        $queryFilterManager = $options['query_filter_manager'] ?? QueryFilterManager::class;

        return new $className(
            $entityName,
            $this->buildService($container, QueryService::class, $options['query_service'] ?? [], $requestedName),
            $this->getService($container, $queryFilterManager, $requestedName)
        );
    }
}
