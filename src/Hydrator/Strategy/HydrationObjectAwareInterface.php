<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Hydrator\Strategy;

/**
 * Strategy class that is aware of the object that is being hydrated
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Hydrator\Strategy
 */
interface HydrationObjectAwareInterface
{
    /**
     * @param object|null $object
     */
    public function setObject(?object $object): void;

    /**
     * @return object|null
     */
    public function getObject(): ?object;
}
