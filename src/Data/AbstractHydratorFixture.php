<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Data;

use Laminas\Hydrator\HydratorInterface;

/**
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Data
 */
abstract class AbstractHydratorFixture extends AbstractFixture
{
    /**
     * The fully qualified class name to create and hydrate
     *
     * @var string
     */
    protected string $className;

    /**
     * The hydrator used to construct the instance
     *
     * @var HydratorInterface
     */
    protected HydratorInterface $hydrator;

    /**
     * An array of configuration data
     *
     * @var array<mixed>
     */
    protected array $data;

    /**
     * @param HydratorInterface $hydrator
     * @param array<mixed>      $data
     */
    public function __construct(HydratorInterface $hydrator, array $data)
    {
        $this->hydrator = $hydrator;
        $this->data = $data;
    }
}
