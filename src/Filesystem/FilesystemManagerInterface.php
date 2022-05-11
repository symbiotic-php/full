<?php

namespace Symbiotic\Filesystem;


interface FilesystemManagerInterface
{
    public function getDisks():array;
    /**
     * Get a filesystem instance.
     *
     * @param string $name
     * @return FilesystemInterface
     */
    public function disk(string $name = null);


    /**
     * @return array
     *@see FilesystemManagerInterface::addDriver()
     */
    public function getDrivers():array;
    /**
     * @param array $config
     * @return AdapterInterface
     */
    public function createLocalDriver(array $config);


    /**
     * Set the given disk instance.
     *
     * @param string $name
     * @param mixed $disk
     * @return $this
     */
    public function set(string $name, $disk);


    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver();

    /**
     * Get the default cloud driver name.
     *
     * @return string
     */
    public function getDefaultCloudDriver();

    /**
     * Unset the given disk instances.
     *
     * @param array|string $disk
     * @return $this
     */
    public function forgetDisk($disk);

    /**
     * Register a custom driver creator Closure.
     *
     * @param string $driver
     * @param \Closure $callback
     * @return $this
     */
    public function addDriver(string $driver, \Closure $callback);

}
