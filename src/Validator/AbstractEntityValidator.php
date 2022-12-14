<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Validator;

use Arp\Entity\EntityInterface;
use Arp\LaminasDoctrine\Repository\EntityRepositoryInterface;
use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Exception\RuntimeException;

abstract class AbstractEntityValidator extends AbstractValidator
{
    public const CRITERIA_EMPTY = 'criteria_empty';

    /**
     * @param EntityRepositoryInterface<EntityInterface> $repository
     * @param array<int, string> $fieldNames
     * @param array<mixed>|null $options
     */
    public function __construct(
        protected EntityRepositoryInterface $repository,
        protected array $fieldNames,
        ?array $options = null
    ) {
        parent::__construct($options);
    }

    /**
     * @param array<mixed> $criteria
     *
     * @throws RuntimeException
     */
    protected function getMatchedEntity(array $criteria): ?EntityInterface
    {
        try {
            return $this->repository->findOneBy($criteria);
        } catch (\Exception $e) {
            throw new RuntimeException(
                sprintf(
                    'Failed to validate for entity \'%s\': %s',
                    $this->repository->getClassName(),
                    $e->getMessage()
                ),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @param mixed $value
     * @param array<mixed> $additionalData
     *
     * @return array<mixed>
     */
    protected function getMatchCriteria(mixed $value, array $additionalData): array
    {
        $criteria = [];
        foreach ($this->fieldNames as $fieldName) {
            if (empty($criteria[$fieldName]) && !empty($additionalData[$fieldName])) {
                $criteria[$fieldName] = $additionalData[$fieldName];
            }
        }

        // Let's add in the existing value if not already set
        if (!empty($this->fieldNames[0]) && !array_key_exists($this->fieldNames[0], $criteria)) {
            $criteria[$this->fieldNames[0]] = $value;
        }

        return $criteria;
    }
}
