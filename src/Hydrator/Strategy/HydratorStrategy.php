<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Hydrator\Strategy;

use Arp\DoctrineEntityRepository\EntityRepositoryInterface;
use Laminas\Hydrator\Strategy\Exception\InvalidArgumentException;
use Laminas\Hydrator\Strategy\HydratorStrategy as LaminasHydratorStrategy;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Hydrator\Strategy
 */
final class HydratorStrategy extends AbstractHydratorStrategy
{
    /**
     * @var LaminasHydratorStrategy
     */
    private LaminasHydratorStrategy $hydratorStrategy;

    /**
     * @param EntityRepositoryInterface $repository
     * @param LaminasHydratorStrategy   $hydratorStrategy
     */
    public function __construct(EntityRepositoryInterface $repository, LaminasHydratorStrategy $hydratorStrategy)
    {
        parent::__construct($repository);

        $this->hydratorStrategy = $hydratorStrategy;
    }

    /**
     * @param mixed       $value
     * @param object|null $object
     *
     * @return mixed|null
     *
     * @throws InvalidArgumentException
     */
    public function extract($value, ?object $object = null)
    {
        if (null !== $value) {
            try {
                return $this->hydratorStrategy->extract($value, $object);
            } catch (\Throwable $e) {
                throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
            }
        }
        return null;
    }

    /**
     * @param mixed             $value
     * @param array<mixed>|null $data
     *
     * @return mixed|object|string|null
     *
     * @throws InvalidArgumentException
     */
    public function hydrate($value, ?array $data)
    {
        $id = $this->resolveId($value);

        if (!empty($id)) {
            try {
                $entity = $this->repository->findOneBy(['id' => $id]);

                $targetClassName = $this->repository->getClassName();
                if ($entity instanceof $targetClassName) {
                    return $entity;
                }
            } catch (\Throwable $e) {
            }

            $value = [
                'id' => $value,
            ];
        }

        try {
            return $this->hydratorStrategy->hydrate($value, $data);
        } catch (\Throwable $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
