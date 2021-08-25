<?php

namespace Dissonance\Container;

/**
 * Interface ArrayContainerInterface
 * @package Dissonance\Container
 *
 * @see \Dissonance\Container\MagicAccessTrait  realisation trait (package: dissonance/container-traits)
 */
interface MagicAccessInterface
{

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
     * @return mixed
     *
     * @throws  \Exception
     */
    public function __get(string $key);

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
     * @see \Psr\Container\ContainerInterface::get()
     *
     * @return mixed|null
     */
    public function __invoke($key, $default = null);
}
