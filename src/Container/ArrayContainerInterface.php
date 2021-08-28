<?php

namespace Symbiotic\Container;

/**
 * Interface ArrayContainerInterface
 * @package Symbiotic\Container
 *
 * @see \Symbiotic\Container\BaseContainerTrait
 * @see \Symbiotic\Container\ArrayAccessTrait  and ArrayAccess realisation trait
 */
interface ArrayContainerInterface extends BaseContainerInterface, \ArrayAccess
{

}
