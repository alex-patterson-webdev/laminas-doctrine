<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Service\Connection;

use Arp\LaminasDoctrine\Config\DoctrineConfig;
use Arp\LaminasDoctrine\Service\Connection\Exception\ConnectionFactoryException;
use Arp\LaminasDoctrine\Service\Connection\Exception\ConnectionManagerException;
use Doctrine\DBAL\Connection;

/**
 * @deprecated
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Service\Connection
 */
final class ConnectionManager implements ConnectionManagerInterface
{
    /**
     * @var DoctrineConfig
     */
    private DoctrineConfig $config;

    /**
     * @var Connection[]
     */
    private array $connections = [];

    /**
     * @var ConnectionFactoryInterface
     */
    private ConnectionFactoryInterface $factory;

    /**
     * @param DoctrineConfig             $config
     * @param ConnectionFactoryInterface $factory
     * @param Connection[]               $connections
     */
    public function __construct(
        DoctrineConfig $config,
        ConnectionFactoryInterface $factory,
        array $connections
    ) {
        $this->config = $config;
        $this->factory = $factory;

        $this->setConnections($connections);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasConnection(string $name): bool
    {
        return isset($this->connections[$name]) || $this->config->hasConnectionConfig($name);
    }

    /**
     * @param string $name
     *
     * @return Connection
     *
     * @throws ConnectionManagerException
     */
    public function getConnection(string $name): Connection
    {
        if (!isset($this->connections[$name]) && $this->config->hasConnectionConfig($name)) {
            $this->connections[$name] = $this->create($name, $this->config->getConnectionConfig($name));
        }

        if (isset($this->connections[$name])) {
            return $this->connections[$name];
        }

        throw new ConnectionManagerException(
            sprintf('Failed to establish connection \'%s\': Failed to find a the required configuration', $name)
        );
    }

    /**
     * @param string       $name
     * @param array<mixed> $config
     *
     * @return Connection
     *
     * @throws ConnectionManagerException
     */
    private function create(string $name, array $config): Connection
    {
        try {
            return $this->factory->create($config);
        } catch (ConnectionFactoryException $e) {
            throw new ConnectionManagerException(
                sprintf('Failed to establish connection \'%s\': %s', $name, $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @param array<Connection|array> $connections
     */
    public function setConnections(array $connections): void
    {
        $this->connections = [];

        foreach ($connections as $name => $connection) {
            if (is_array($connection)) {
                $this->addConnectionConfig($name, $connection);
            } else {
                $this->setConnection($name, $connection);
            }
        }
    }

    /**
     * @param string     $name
     * @param Connection $connection
     */
    public function setConnection(string $name, Connection $connection): void
    {
        $this->connections[$name] = $connection;
    }

    /**
     * @param string       $name
     * @param array<mixed> $config
     */
    public function addConnectionConfig(string $name, array $config): void
    {
        $this->config->setConnectionConfig($name, $config);
    }
}
