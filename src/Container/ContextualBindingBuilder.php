<?php

namespace Dissonance\Container;

use Dissonance\Container\DIContainerInterface;
use Dissonance\Core\Support\Arr;
class ContextualBindingBuilder
{
    /**
     * The underlying container instance.
     *
     * @var \Dissonance\Container\DIContainerInterface
     */
    protected $container;

    /**
     * The concrete instance.
     *
     * @var string|array
     */
    protected $concrete;

    /**
     * The abstract target.
     *
     * @var string
     */
    protected $needs;

    /**
     * Create a new contextual binding builder.
     *
     * @param  \Dissonance\Container\DIContainerInterface  $container
     * @param  string|array  $concrete
     * @return void
     */
    public function __construct(DIContainerInterface $container, $concrete)
    {
        $this->concrete = $concrete;
        $this->container = $container;
    }

    /**
     * Define the abstract target that depends on the context.
     *
     * @param  string  $abstract
     * @return $this
     */
    public function needs(string $abstract)
    {
        $this->needs = $abstract;

        return $this;
    }

    /**
     * Define the implementation for the contextual binding.
     *
     * @param  \Closure|mixed  $implementation
     * @return void
     */
    public function give($implementation)
    {
        $concretes = $this->concrete;
        foreach ((!empty($concretes)?(array)$concretes:[]) as $concrete) {
            $this->container->addContextualBinding($concrete, $this->needs, $implementation);
        }
    }
}
