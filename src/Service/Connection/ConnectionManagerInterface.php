<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Service\Connection;

use Arp\LaminasDoctrine\Service\Connection\Exception\ConnectionManagerException;
use Doctrine\DBAL\Connection;

interface ConnectionManagerInterface
{
    public function hasConnection(string $name): bool;

    /**
     * @throws ConnectionManagerException
     */
    public function getConnection(string $name): Connection;

    /**
     * @param Connection[]|array<mixed> $connections
     */
    public function setConnections(array $connections): void;

    public function setConnection(string $name, Connection $connection): void;

    /**
     * @param array<mixed> $config
     */
    public function addConnectionConfig(string $name, array $config): void;
}
