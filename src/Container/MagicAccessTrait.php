<?php

namespace Dissonance\Container;


/**
 * Trait MagicAccessTrait
 * @package Dissonance\Container
 *
 * @method bool has(string $key)
 * @uses \Dissonance\Container\BaseContainerInterface::has()
 * @uses BaseContainerTrait::has()
 *
 * @method mixed|null get(string $key)
 * @uses \Dissonance\Container\BaseContainerInterface::get()
 * @uses BaseContainerTrait::get()
 *
 * @method void set(string $key, $value)
 * @uses \Dissonance\Container\BaseContainerInterface::set()
 * @uses BaseContainerTrait::set()
 *
 * @method bool delete(string $key)
 * @uses \Dissonance\Container\BaseContainerInterface::delete()
 * @uses BaseContainerTrait::delete()
 */
trait MagicAccessTrait /*implements \Dissonance\Container\MagicAccessInterface*/
{

    public function __get($key)
    {
        return $this->get($key);
    }

    public function __set(string $key, $value): void
    {
        $this->set($key, $value);
    }

    public function __unset(string $key): void
    {
        $this->delete($key);
    }

    public function __isset($key): bool
    {
        return $this->has($key);
    }

    /**
     * Special get Method with default
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function __invoke($key, $default = null)
    {
        return $this->has($key) ? $this->get($key) : (\is_callable($default) ? $default() : $default);
    }
}