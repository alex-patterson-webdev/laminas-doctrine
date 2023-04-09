<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\DBAL\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\BigIntType;

class IntegerBigIntType extends BigIntType
{
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?int
    {
        return $value === null ? null : (int)$value;
    }
}
