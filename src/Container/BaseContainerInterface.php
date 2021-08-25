<?php

namespace Dissonance\Container;



/**
 * Interface BaseContainerInterface
 *
 * A less strict, augmented implementation.
 *
 * The name of the interface is specially different, so as not to be confused with the interface from PSR.
 * Using aliases is not recommended.
 *
 * @package Dissonance\Container
 *
 * Extenders this interface
 * @see ArrayContainerInterface
 * @see MultipleAccessInterface
 * @see MagicAccessInterface
 *
 */
interface BaseContainerInterface
{

    /**
     * Get item by key
     *
     * @param string $key
     */
    public function get(string $key);

    /**
     * Checking the presence of data in the container by its key
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Put a key / value pair or array of key / value pairs.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function set(string $key, $value): void;


    /**
     * Remove value by key
     *
     * @param string $key
     *
     * @return bool
     */
    public function delete(string $key): bool;


}
