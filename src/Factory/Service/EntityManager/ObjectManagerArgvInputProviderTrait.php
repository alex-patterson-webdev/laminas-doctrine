<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Factory\Service\EntityManager;

use Symfony\Component\Console\Input\ArgvInput;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Factory\Service
 */
trait ObjectManagerArgvInputProviderTrait
{
    /**
     * @var string
     */
    private string $parameterOption = '--object-manager';

    /**
     * @var string
     */
    private string $defaultEntityManagerArgvInput = '';

    /**
     * @return string
     */
    public function getEntityManagerArgvInput(): string
    {
        $arguments = new ArgvInput();

        if ($arguments->hasParameterOption($this->parameterOption)) {
            return $arguments->getParameterOption($this->parameterOption);
        }

        return $this->defaultEntityManagerArgvInput;
    }

    /**
     * @param string $defaultEntityManagerArgvInput
     */
    public function setDefaultEntityManagerArgvInput(string $defaultEntityManagerArgvInput): void
    {
        $this->defaultEntityManagerArgvInput = $defaultEntityManagerArgvInput;
    }
}
