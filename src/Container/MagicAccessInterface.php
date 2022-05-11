<?php

namespace Symbiotic\Container;

/**
 * Interface ArrayContainerInterface
 * @package Symbiotic\Container
 *
 * @see \Symbiotic\Container\MagicAccessTrait  realisation trait (package: symbiotic/container-traits)
 */
interface MagicAccessInterface
{

    /**
     * @param string $key
     * @return mixed
     *
     * @throws  \Exception
     */
    public function __get(string $key);

    /**
     * Set item
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function __set(string $key, $value): void;

    /**
     * @param string $key
     * @return bool
     */
    public function __isset(string $key): bool;

    /**
     * @param string $key
     *
     * @return void
     */
    public function __unset(string $key): void;

    /**
     * Special get Method with default
     *
     * @param $key
     * @param null $default
     *
     * @return mixed|null
     * @see \Psr\Container\ContainerInterface::get()
     *
     */
    public function __invoke($key, $default = null);
}
