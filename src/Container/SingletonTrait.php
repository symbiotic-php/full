<?php

namespace Dissonance\Container;

trait SingletonTrait
{
    /**
     * The current globally available container (if any).
     *
     * @var static
     */
    protected static $instance;

    /**
     * Set the globally available instance of the container.
     *
     * @return static
     */
    public static function getInstance()
    {
        return null===static::$instance
            ? static::$instance = new static()
            : static::$instance;
    }
}
