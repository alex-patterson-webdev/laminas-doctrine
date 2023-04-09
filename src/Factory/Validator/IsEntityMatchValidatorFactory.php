<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Validator;

use Arp\LaminasDoctrine\Validator\IsEntityMatchValidator;

final class IsEntityMatchValidatorFactory extends AbstractEntityValidatorFactory
{
    /**
     * @return class-string<IsEntityMatchValidator>
     */
    protected function getDefaultClassName(): string
    {
        return IsEntityMatchValidator::class;
    }
}
