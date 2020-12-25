<?php

declare(strict_types=1);

namespace Arp\LaminasDoctrine\Hydrator;

/**
 * When using hydrators and PHP 7.4+ type hinted properties, there will be times where our entity classes will be
 * instantiated via reflection (due to the Doctrine/Laminas hydration processes). This instantiation will bypass the
 * entity's __construct and therefore not initialise the default class property values. This will lead to
 * "Typed property must not be accessed before initialization" fatal errors, despite using the hydrators in their
 * intended way.
 *
 * This class will provide a isInitialisedFieldName() method, which uses a ReflectionProperty instance to check if
 * the property has been initialised before it attempts to use the value.
 *
 * @author  Alex Patterson <alex.patterson.webdev@gmail.com>
 * @package Arp\LaminasDoctrine\Hydrator
 */
abstract class AbstractHydrator extends \Laminas\Hydrator\AbstractHydrator
{

}
