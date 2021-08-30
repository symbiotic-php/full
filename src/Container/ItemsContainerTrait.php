<?php

namespace Symbiotic\Container;


/**
 * Trait BaseContainerTrait
 *
 * Менее универсальный, но рабоает на 35% быстрее {@see \BaseContainerTrai}
 *
 * @package symbiotic/container-traits
 *
 */
trait ItemsContainerTrait /*implements \Symbiotic\Container\BaseContainerInterface*/
{


    protected $items = [];
    /**
     * @param string $key
     *
     * @param $default
     *
     * @return mixed|null
     */
    public function get($key)
    {
        $items = &$this->items;
        return $this->hasBy($key,$items) ? $items[$key] :
            (
            is_callable($default = \func_num_args() === 2 ? \func_get_arg(1) : null)
                ? $default() : $default
            );
    }

    /**
     * @param string|int $key
     * @info
     * @return bool
     */
    public function has($key): bool
    {
        return $this->hasBy($key,$this->items);
    }

    /**
     * @param string|int $key
     * @param array|\ArrayAccess $items
     * @return bool
     * @info
     */
    private function hasBy($key, array &$items): bool
    {
        return isset($items[$key]) // isset в 4 раза быстрее array_key_exists
            ||  (is_array($items) && array_key_exists($key, $items))
            || ($items instanceof \ArrayAccess && $items->offsetExists($key));
    }

    /**
     * @param int|string $key
     * @param $value
     */
    public function set($key, $value): void
    {
        $this->items[$key] = $value;
    }


    /**
     * @param int|string $key
     * @return bool
     */
    public function delete($key): bool
    {
        unset($this->items[$key]);

        return true;
    }

}
