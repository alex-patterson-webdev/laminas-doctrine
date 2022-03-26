<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Repository\Query;

use Arp\DoctrineEntityRepository\Query\QueryService;
use Arp\DoctrineEntityRepository\Query\QueryServiceInterface;
use Arp\LaminasDoctrine\Factory\Service\EntityManager\EntityManagerFactoryProviderTrait;
use Arp\LaminasFactory\AbstractFactory;
use Arp\LaminasMonolog\Factory\FactoryLoggerProviderTrait;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Repository\Query
 */
class QueryServiceFactory extends AbstractFactory
{
    use EntityManagerFactoryProviderTrait;
    use FactoryLoggerProviderTrait;

    /**
     * @param ContainerInterface&ServiceLocatorInterface $container
     * @param string                                     $requestedName
     * @param array<string, mixed>|null                  $options
     *
     * @return QueryServiceInterface
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

        /** @var QueryServiceInterface $queryService */
        /** @noinspection PhpUnnecessaryLocalVariableInspection */
        $queryService = new $className(
            $entityName,
            $entityManager,
            $this->getLogger($container, $options['logger'] ?? null, $requestedName)
        );

        return $queryService;
    }
}
