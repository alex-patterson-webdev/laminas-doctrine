<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Hydrator\Strategy\Exception;

use Laminas\Hydrator\Strategy\Exception\ExceptionInterface;

class RuntimeException extends \RuntimeException implements ExceptionInterface
{
}
