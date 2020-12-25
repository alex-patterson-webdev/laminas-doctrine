<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Service;

use Arp\LaminasDoctrine\Service\Exception\ConfigurationFactoryException;
use Doctrine\ORM\Configuration;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Service
 */
interface ConfigurationFactoryInterface
{
    /**
     * @param array $config
     *
     * @return Configuration
     *
     * @throws ConfigurationFactoryException
     */
    public function create(array $config): Configuration;
}
