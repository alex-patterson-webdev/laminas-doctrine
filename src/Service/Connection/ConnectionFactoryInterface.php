<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Service\Connection;

use Arp\LaminasDoctrine\Service\Connection\Exception\ConnectionFactoryException;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;

interface ConnectionFactoryInterface
{
    /**
     * @param array<mixed>              $config
     * @param Configuration|string|null $configuration
     *
     * @throws ConnectionFactoryException
     */
    public function create(array $config, $configuration = null, ?EventManager $eventManager = null): Connection;
}
