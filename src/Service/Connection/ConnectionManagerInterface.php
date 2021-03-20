<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Service\Connection;

use Arp\LaminasDoctrine\Service\Connection\Exception\ConnectionManagerException;
use Doctrine\DBAL\Connection;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Service\Connection
 */
interface ConnectionManagerInterface
{
    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasConnection(string $name): bool;

    /**
     * @param string $name
     *
     * @return Connection
     *
     * @throws ConnectionManagerException
     */
    public function getConnection(string $name): Connection;

    /**
     * @param Connection[]|array[] $connections
     */
    public function setConnections(array $connections): void;

    /**
     * @param string     $name
     * @param Connection $connection
     */
    public function setConnection(string $name, Connection $connection): void;

    /**
     * @param string       $name
     * @param array<mixed> $config
     */
    public function addConnectionConfig(string $name, array $config): void;
}
