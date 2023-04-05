<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Validator;

use Laminas\Validator\Exception\RuntimeException;

final class IsEntityNoMatchValidator extends AbstractEntityValidator
{
    public const IS_MATCH = 'is_match';

    /**
     * @var array<string, string>
     */
    protected array $messageTemplates = [
        self::CRITERIA_EMPTY => 'No criteria was provided',
        self::IS_MATCH => 'Value matched an existing entity',
    ];

    /**
     * @param mixed $value
     * @param array<string, mixed> $context
     *
     * @throws RuntimeException
     */
    public function isValid(mixed $value, array $context = []): bool
    {
        $criteria = $this->getMatchCriteria($value, $context);
        if (empty($criteria)) {
            $this->error(self::CRITERIA_EMPTY);
            return false;
        }

        // We would like to ensure that the criteria will NOT result in an entity being matched
        $match = $this->getMatchedEntity($criteria);
        if (null === $match) {
            return true;
        }

        // We matched at least one entity with the provided criteria
        $this->error(self::IS_MATCH, $criteria);
        return false;
    }
}
