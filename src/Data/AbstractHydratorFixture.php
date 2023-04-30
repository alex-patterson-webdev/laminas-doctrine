<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Data;

use Laminas\Hydrator\HydratorInterface;

abstract class AbstractHydratorFixture extends AbstractFixture
{
    /**
     * @var class-string
     */
    protected string $className;

    protected HydratorInterface $hydrator;

    /**
     * @var array<mixed>
     */
    protected array $data;

    /**
     * @param array<mixed> $data
     */
    public function __construct(HydratorInterface $hydrator, array $data)
    {
        $this->hydrator = $hydrator;
        $this->data = $data;
    }
}
