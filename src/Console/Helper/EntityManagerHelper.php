<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Console\Helper;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Helper\Helper;

final class EntityManagerHelper extends Helper
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function getName(): string
    {
        return 'entityManager';
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }
}
