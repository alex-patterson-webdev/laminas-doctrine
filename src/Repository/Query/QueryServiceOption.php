<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Repository\Query;

final class QueryServiceOption
{
    public const ORDER_BY = 'order_by';
    public const ASSOCIATION = 'association';
    public const HINTS = 'hints';
    public const LOCK_MODE = 'lock_mode';
    public const ENTITY = 'entity';
    public const FIRST_RESULT = 'first_result';
    public const MAX_RESULTS = 'max_results';
    public const DQL = 'dql';
    public const HYDRATION_MODE = 'hydration_mode';
    public const PARAMS = 'params';
    public const FETCH_MODE = 'fetch_mode';
}
