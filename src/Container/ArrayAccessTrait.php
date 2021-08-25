<?php

namespace Dissonance\Container;


/**
 * Trait ArrayAccessTrait
 *
 * @package dissonance/container-traits
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
trait ArrayAccessTrait /*implements \Dissonance\Container\ArrayContainerInterface */
{

    /**
     * Get an item at a given offset.
     *
     * @param  mixed  $key
     * @return mixed
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * Get an item at a given offset.
     *
     * @param  mixed $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Set the item at a given offset.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Unset the item at a given offset.
     *
     * @param  string  $key
     * @return void
     */
    public function offsetUnset($key)
    {
        $this->delete($key);
    }

}
