<?php

namespace Symbiotic\Auth;


interface AuthStorageInterface
{
    /**
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * Returns the contents of storage
     *
     * Behavior is undefined when storage is empty.
     *
     * @return mixed
     * @throws
     */
    public function read();

    /**
     * Writes $contents to storage
     *
     * @param mixed $contents
     * @return void
     * @throws
     */
    public function write($contents);

    /**
     * Clears contents from storage
     *
     * @return void
     * @throws
     */
    public function clear();
}