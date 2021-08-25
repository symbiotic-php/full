<?php

namespace Dissonance\Container;


/**
 * Trait BaseContainerTrait
 *
 * @package dissonance/container-traits
 *
 * Старая версия, из-за getContainerItems на 30% дольше рабоает, но универсальней
 */
trait BaseContainerTrait /*implements \Dissonance\Container\BaseContainerInterface*/
{

    /**
     * A special method for returning data by reference and managing it out
     *
     * @return array|\ArrayAccess
     * @todo: Can do protected, on the one hand it is convenient, but to give everyone in a row to manage is not correct!?
     */
    abstract protected function &getContainerItems(): array|\ArrayAccess;

    /**
     * @param string $key
     *
     * @param $default
     *
     * @return mixed|null
     */
    public function get(string|int $key)
    {
        $items = &$this->getContainerItems();
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
    public function has(string|int $key): bool
    {
        $items = &$this->getContainerItems();
        return $this->hasBy($key,$items);
    }

    /**
     * @param string|int $key
     * @param array|\ArrayAccess $items
     * @return bool
     * @info
     */
    private function hasBy(string|int $key, \ArrayAccess|array &$items): bool
    {
        return isset($items[$key]) // isset в 4 раза быстрее array_key_exists
            ||  (is_array($items) && array_key_exists($key, $items))
            || ($items instanceof \ArrayAccess && $items->offsetExists($key));
    }

    /**
     * @param int|string $key
     * @param $value
     */
    public function set(string|int $key, $value): void
    {
        $items = &$this->getContainerItems();
        $items[$key] = $value;
    }


    /**
     * @param int|string $key
     * @return mixed
     */
    public function delete(string|int $key): bool
    {
        $items = &$this->getContainerItems();
        unset($items[$key]);

        return true;
    }

}
