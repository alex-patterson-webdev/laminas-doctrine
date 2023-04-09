<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Service\EntityManager;

use Symfony\Component\Console\Input\ArgvInput;

trait ObjectManagerArgvInputProviderTrait
{
    private string $parameterOption = '--object-manager';

    private string $defaultEntityManagerArgvInput = 'orm_default';

    public function getEntityManagerArgvInput(): string
    {
        $arguments = new ArgvInput();

        if ($arguments->hasParameterOption($this->parameterOption)) {
            return $arguments->getParameterOption($this->parameterOption);
        }

        return $this->defaultEntityManagerArgvInput;
    }

    public function setDefaultEntityManagerArgvInput(string $defaultEntityManagerArgvInput): void
    {
        $this->defaultEntityManagerArgvInput = $defaultEntityManagerArgvInput;
    }
}
