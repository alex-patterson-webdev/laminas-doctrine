<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Service\Connection;

use Arp\LaminasDoctrine\Config\ConnectionConfigs;
use Arp\LaminasDoctrine\Service\Connection\Exception\ConnectionFactoryException;
use Arp\LaminasDoctrine\Service\Connection\Exception\ConnectionManagerException;
use Doctrine\DBAL\Connection;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Service
 */
final class ConnectionManager implements ConnectionManagerInterface
{
    /**
     * @var ConnectionConfigs
     */
    private ConnectionConfigs $configs;

    /**
     * @var Connection[]
     */
    private array $connections = [];

    /**
     * @var ConnectionFactoryInterface
     */
    private ConnectionFactoryInterface $factory;

    /**
     * @param ConnectionConfigs          $configs
     * @param ConnectionFactoryInterface $factory
     * @param Connection[]               $connections
     */
    public function __construct(
        ConnectionConfigs $configs,
        ConnectionFactoryInterface $factory,
        array $connections
    ) {
        $this->configs = $configs;
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
        return isset($this->connections[$name]) || $this->configs->hasConnectionConfig($name);
    }

    /**
     * @param string $name
     *
     * @return Connection
     *
     * @throws \Arp\LaminasDoctrine\Service\Connection\Exception\ConnectionManagerException
     */
    public function getConnection(string $name): Connection
    {
        if (!isset($this->connections[$name]) && $this->configs->hasConnectionConfig($name)) {
            $this->connections[$name] = $this->create($name, $this->configs->getConnectionConfig($name));
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
     * @throws \Arp\LaminasDoctrine\Service\Connection\Exception\ConnectionManagerException
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
        $this->configs->setConnectionConfig($name, $config);
    }
}
