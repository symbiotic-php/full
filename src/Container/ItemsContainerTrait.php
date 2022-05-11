<?php

namespace Symbiotic\Container;


/**
 * Trait ItemsContainerTrait
 *
 * Менее универсальный, но рабоает на 35% быстрее {@see \BaseContainerTrai}
 *
 * @package symbiotic/container-traits
 *
 */
trait ItemsContainerTrait /* implements \Symbiotic\Container\BaseContainerInterface */
{


    protected $items = [];

    /**
     * @param int|string $key
     *
     * @return mixed|null
     */
    public function get(string|int $key)
    {
        $items = &$this->items;
        return $this->hasBy($key, $items) ? $items[$key] :
            (
            is_callable($default = \func_num_args() === 2 ? \func_get_arg(1) : null)
                ? $default() : $default
            );
    }

    /**
     * @param string|int $key
     * @param array|\ArrayAccess $items
     * @return bool
     * @info
     */
    private function hasBy(string|int $key, array &$items): bool
    {
        return isset($items[$key]) // isset в 4 раза быстрее array_key_exists
            || (is_array($items) && array_key_exists($key, $items))
            || ($items instanceof \ArrayAccess && $items->offsetExists($key));
    }

    /**
     * @param string|int $key
     * @info
     * @return bool
     */
    public function has(string|int $key): bool
    {
        return $this->hasBy($key, $this->items);
    }

    /**
     * @param int|string $key
     * @param $value
     */
    public function set(string|int $key, $value): void
    {
        $this->items[$key] = $value;
    }


    /**
     * @param int|string $key
     * @return bool
     */
    public function delete(string|int $key): bool
    {
        unset($this->items[$key]);

        return true;
    }

}
