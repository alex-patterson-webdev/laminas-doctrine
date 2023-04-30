<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Hydrator\Strategy;

use Arp\Entity\EntityInterface;
use Arp\LaminasDoctrine\Repository\EntityRepositoryInterface;
use Laminas\Hydrator\Strategy\StrategyInterface;

abstract class AbstractHydratorStrategy implements StrategyInterface
{
    /**
     * @var EntityRepositoryInterface<EntityInterface>
     */
    protected EntityRepositoryInterface $repository;

    /**
     * @param EntityRepositoryInterface<EntityInterface> $repository
     */
    public function __construct(EntityRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param array<mixed>|EntityInterface|string|int $value
     */
    protected function resolveId(mixed $value): mixed
    {
        if (is_array($value)) {
            $id = $value['id'] ?? null;
            unset($value['id']);
        } elseif ($value instanceof EntityInterface) {
            $id = $value->getId();
        } else {
            $id = $value;
        }

        return $id;
    }
}
