<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Service\Configuration;

use Arp\LaminasDoctrine\Service\Configuration\Exception\ConfigurationFactoryException;
use Doctrine\ORM\Configuration;

interface ConfigurationFactoryInterface
{
    /**
     * @param array<string, mixed> $config
     *
     * @throws ConfigurationFactoryException
     */
    public function create(array $config): Configuration;
}
