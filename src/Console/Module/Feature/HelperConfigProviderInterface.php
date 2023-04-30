<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Console\Module\Feature;

interface HelperConfigProviderInterface
{
    /**
     * @return array<mixed>
     */
    public function getConsoleHelperManagerConfig(): array;
}
