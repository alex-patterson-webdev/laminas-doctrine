<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Service;

use Arp\LaminasDoctrine\Config\DoctrineConfig;
use Arp\LaminasDoctrine\Service\Connection\ConnectionFactory;
use Arp\LaminasDoctrine\Service\Connection\ConnectionManager;
use Arp\LaminasFactory\AbstractFactory;
use Doctrine\DBAL\Connection;
use Interop\Container\ContainerInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Service
 */
final class ConnectionManagerFactory extends AbstractFactory
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return ConnectionManager
     *
     * @noinspection PhpMissingParamTypeInspection
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ConnectionManager
    {
        /** @var DoctrineConfig $doctrineConfig */
        $doctrineConfig = $this->getService($container, DoctrineConfig::class, $requestedName);

        /** @var ConnectionFactory $connectionFactory */
        $connectionFactory = $this->getService($container, ConnectionFactory::class, $requestedName);

        /** @var Connection[] $connections */
        $connections = [];

        return new ConnectionManager($doctrineConfig, $connectionFactory, $connections);
    }
}
