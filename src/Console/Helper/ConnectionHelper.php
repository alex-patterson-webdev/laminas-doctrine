<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Console\Helper;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Helper\Helper;

final class ConnectionHelper extends Helper
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function getName(): string
    {
        return 'connection';
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }
}
