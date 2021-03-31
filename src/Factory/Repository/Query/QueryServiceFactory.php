<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Repository\Query;

use Arp\DoctrineEntityRepository\Query\QueryService;
use Arp\DoctrineEntityRepository\Query\QueryServiceInterface;
use Arp\LaminasDoctrine\Factory\Service\EntityManagerFactoryProviderTrait;
use Arp\LaminasFactory\AbstractFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Repository\Query
 */
class QueryServiceFactory extends AbstractFactory
{
    use EntityManagerFactoryProviderTrait;

    /**
     * @param ContainerInterface        $container
     * @param string                    $requestedName
     * @param array<string, mixed>|null $options
     *
     * @return QueryServiceInterface
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
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

        return new $className(
            $entityName,
            $entityManager,
            $this->getLogger($container, $options['logger'] ?? null, $requestedName)
        );
    }

    /**
     * @param ContainerInterface          $container
     * @param LoggerInterface|string|null $logger
     * @param string                      $serviceName
     *
     * @return LoggerInterface
     *
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     */
    private function getLogger(ContainerInterface $container, $logger, string $serviceName): LoggerInterface
    {
        if (null === $logger) {
            return new NullLogger();
        }

        if (is_string($logger)) {
            $logger = $this->getService($container, $logger, $serviceName);
        }

        if (!$logger instanceof LoggerInterface) {
            throw new ServiceNotCreatedException(
                sprintf(
                    'The logger must be of type \'%s\'; \'%s\' provided for service \'%s\'',
                    LoggerInterface::class,
                    is_object($logger) ? get_class($logger) : gettype($logger),
                    $serviceName
                )
            );
        }

        return $logger;
    }
}
