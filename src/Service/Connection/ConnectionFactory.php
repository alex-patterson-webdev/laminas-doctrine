<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Service\Connection;

use Arp\LaminasDoctrine\Service\Exception\ConnectionFactoryException;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDO\MySQL\Driver;
use Doctrine\DBAL\DriverManager;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Service\Connection
 */
final class ConnectionFactory implements ConnectionFactoryInterface
{
    /**
     * @var ConfigurationManager
     */
    private ConfigurationManager $configurationManager;

    /**
     * @param ConfigurationManager $configurationManager
     */
    public function __construct(ConfigurationManager $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    /**
     * Create a new connection from the provided $config
     *
     * @param array                     $config
     * @param Configuration|string|null $configuration
     * @param EventManager|string|null  $eventManager
     *
     * @return Connection
     *
     * @throws ConnectionFactoryException
     */
    public function create(array $config, $configuration = null, $eventManager = null): Connection
    {
        $params = array_merge(
            [
                'driverClass'  => $config['driverClass'] ?? Driver::class,
                'wrapperClass' => $config['wrapperClass'] ?? null,
                'pdo'          => $config['pdo'] ?? null,
            ],
            $config['params'] ?? []
        );

        if (!empty($config['platform'])) {
            $platform = null;
        }

        try {
            if (is_string($configuration)) {
                $configuration = $this->configurationManager->getConfiguration($configuration);
            }

            return DriverManager::getConnection($params, $configuration, $eventManager);
        } catch (\Throwable $e) {
            throw new ConnectionFactoryException(
                sprintf('Failed to create new connection: %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }
}
