<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Service\Configuration;

use Arp\LaminasDoctrine\Service\Configuration\Exception\ConfigurationFactoryException;
use Doctrine\ORM\Configuration;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Service
 */
interface ConfigurationFactoryInterface
{
    /**
     * @param array<string, mixed> $config
     *
     * @return Configuration
     *
     * @throws \Arp\LaminasDoctrine\Service\Configuration\Exception\ConfigurationFactoryException
     */
    public function create(array $config): Configuration;
}
