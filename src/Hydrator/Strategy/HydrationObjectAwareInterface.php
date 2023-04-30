<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Hydrator\Strategy;

/**
 * Strategy class that is aware of the object that is being hydrated
 */
interface HydrationObjectAwareInterface
{
    public function setObject(?object $object): void;

    public function getObject(): ?object;
}
