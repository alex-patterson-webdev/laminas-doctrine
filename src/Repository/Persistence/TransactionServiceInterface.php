<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Repository\Persistence;

interface TransactionServiceInterface
{
    public function beginTransaction(): void;

    public function commitTransaction(): void;

    public function rollbackTransaction(): void;
}
