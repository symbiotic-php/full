<?php

namespace Symbiotic\Container;

/**
 * Interface MultipleAccessInterface
 *
 * @package Symbiotic\Container
 *
 */
interface MultipleAccessInterface
{

    /**
     *
     * @param iterable $keys
     * @return \ArrayAccess|array
     */
    public function getMultiple(iterable $keys);

    /**
     * Set array of key / value pairs.
     *
     * @param iterable $values [ key => value, key2=> val2]
     *
     * @return void
     * @uses set()
     */
    public function setMultiple(iterable $values): void;

    /**
     * Delete multiple values by key
     *
     * @param iterable $keys
     * @return void
     */
    public function deleteMultiple(iterable $keys): bool;


}
