<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Service;

use Arp\LaminasDoctrine\Service\Exception\ConnectionFactoryException;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Service
 */
interface ConnectionFactoryInterface
{
    /**
     * Create a new connection from the provided $params
     *
     * @param array                     $params
     * @param Configuration|string|null $configuration
     * @param EventManager|string|null  $eventManager
     *
     * @return Connection
     *
     * @throws ConnectionFactoryException
     */
    public function create(array $params, $configuration = null, $eventManager = null): Connection;
}
