<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Validator;

use Laminas\Validator\Exception\RuntimeException;

final class IsEntityMatchValidator extends AbstractEntityValidator
{
    public const NO_MATCH = 'no_match';

    /**
     * @var array<string, string>
     */
    protected array $messageTemplates = [
        self::CRITERIA_EMPTY => 'No criteria was provided',
        self::NO_MATCH => 'Value could not be matched to an existing entity',
    ];

    /**
     * @param mixed $value
     * @param array<string, mixed> $context
     *
     * @throws RuntimeException
     */
    public function isValid(mixed $value, array $context = []): bool
    {
        // If we have just one field to check, try for a match with the provided value
        // This prevents issues is the context contains the same named field
        if (
            isset($this->fieldNames[0])
            && 1 === count($this->fieldNames)
            && $this->isMatch([$this->fieldNames[0] => $value])
        ) {
            return true;
        }

        // Fallback to allowing for $context
        $criteria = $this->getMatchCriteria($value, $context);
        if (empty($criteria)) {
            $this->error(self::CRITERIA_EMPTY);
            return false;
        }

        return $this->isMatch($criteria);
    }

    /**
     * @param array<string, mixed> $criteria
     *
     * @throws RuntimeException
     */
    private function isMatch(array $criteria): bool
    {
        $match = $this->getMatchedEntity($criteria);
        if (null !== $match) {
            return true;
        }

        $this->error(self::NO_MATCH);
        return false;
    }
}
