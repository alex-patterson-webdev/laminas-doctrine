<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Validator;

use Arp\LaminasDoctrine\Validator\IsEntityNoMatchValidator;

final class IsEntityNoMatchValidatorFactory extends AbstractEntityValidatorFactory
{
    /**
     * @return class-string<IsEntityNoMatchValidator>
     */
    protected function getDefaultClassName(): string
    {
        return IsEntityNoMatchValidator::class;
    }
}
