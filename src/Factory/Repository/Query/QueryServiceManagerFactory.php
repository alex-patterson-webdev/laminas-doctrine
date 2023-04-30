<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Repository\Query;

use Arp\LaminasDoctrine\Repository\Query\QueryServiceManager;
use Arp\LaminasFactory\AbstractFactory;
use Laminas\ServiceManager\Exception\InvalidArgumentException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

final class QueryServiceManagerFactory extends AbstractFactory
{
    /**
     * @throws InvalidArgumentException
     * @throws ServiceNotCreatedException
     * @throws ServiceNotFoundException
     * @throws ContainerExceptionInterface
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        array $options = null
    ): QueryServiceManager {
        $config = $this->getApplicationOptions($container, 'query_service_manager');

        return new QueryServiceManager($container, $config);
    }
}
