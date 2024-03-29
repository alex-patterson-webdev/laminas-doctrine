<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Service\Connection;

use Arp\LaminasDoctrine\Config\ConnectionConfigs;
use Arp\LaminasDoctrine\Service\Connection\Exception\ConnectionFactoryException;
use Arp\LaminasDoctrine\Service\Connection\Exception\ConnectionManagerException;
use Doctrine\DBAL\Connection;

final class ConnectionManager implements ConnectionManagerInterface
{
    /**
     * @var Connection[]
     */
    private array $connections = [];

    /**
     * @param ConnectionConfigs          $configs
     * @param ConnectionFactoryInterface $factory
     * @param Connection[]               $connections
     */
    public function __construct(
        private readonly ConnectionConfigs $configs,
        private readonly ConnectionFactoryInterface $factory,
        array $connections
    ) {
        $this->setConnections($connections);
    }

    public function hasConnection(string $name): bool
    {
        return isset($this->connections[$name]) || $this->configs->hasConnectionConfig($name);
    }

    /**
     * @throws ConnectionManagerException
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
     * @param array<mixed> $config
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
     * @param array<Connection|array<mixed>> $connections
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

    public function setConnection(string $name, Connection $connection): void
    {
        $this->connections[$name] = $connection;
    }

    /**
     * @param array<mixed> $config
     */
    public function addConnectionConfig(string $name, array $config): void
    {
        $this->configs->setConnectionConfig($name, $config);
    }
}
