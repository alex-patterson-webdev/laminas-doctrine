<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Hydrator\Strategy;

use Arp\Entity\EntityInterface;
use Arp\LaminasDoctrine\Repository\EntityRepositoryInterface;
use Laminas\Hydrator\Strategy\StrategyInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Hydrator\Strategy
 */
abstract class AbstractHydratorStrategy implements StrategyInterface
{
    /**
     * @var EntityRepositoryInterface
     */
    protected EntityRepositoryInterface $repository;

    public function __construct(EntityRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    protected function resolveId($value)
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
