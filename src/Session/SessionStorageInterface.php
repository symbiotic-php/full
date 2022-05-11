<?php

namespace Symbiotic\Session;

use Symbiotic\Container\ArrayContainerInterface;

/**
 * Interface SessionStorageInterface
 * @package Symbiotic\Session
 * @method mixed|null get(string $key) {@see SessionStorageNative::get()}
 * @method bool has(string $key)
 */
interface SessionStorageInterface extends ArrayContainerInterface
{

    /**
     * Start the session, reading the data from a handler.
     *
     * @return bool
     */
    public function start();

    /**
     * Get the current session ID.
     *
     * @return string
     */
    public function getId();

    /**
     * Set the session ID.
     *
     * @param  string  $id
     * @return void
     */
    public function setId(string $id);
    /**
     * Get the name of the session.
     *
     * @return string
     */
    public function getName();







    /**
     * Save the session data to storage.
     *
     * @return bool
     */
    public function save();



    /**
     * Remove all of the items from the session.
     *
     * @return void
     */
    public function clear();


    /**
     * Determine if the session has been started.
     *
     * @return bool
     */
    public function isStarted();


}