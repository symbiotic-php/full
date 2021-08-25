<?php

namespace Dissonance\Container;



/**
 * Trait MultipleAccessTrait
 *
 * @package Dissonance/container-tarits
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
trait MultipleAccessTrait /*implements \Dissonance\Container\MultipleAccessInterface*/
{
    /**
     * @param iterable $keys array keys
     * @return array
     */
    public function getMultiple(iterable $keys)
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key);
        }
        return $result;
    }

    /**
     * Set array of key / value pairs.
     *
     * @param iterable $values [ key => value, key2=> val2]
     *
     * @return void
     * @uses set()
     */
    public function setMultiple(iterable $values): void
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * @param iterable $keys keys array [key1,key2,....]
     *
     * @return bool
     */
    public function deleteMultiple(iterable $keys): bool
    {
        $result = true;
        foreach ($keys as $key) {
            if(!$this->delete($key)){
                $result = false;
            }
        }

        return $result;
    }


}
