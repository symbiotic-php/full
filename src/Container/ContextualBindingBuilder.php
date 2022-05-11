<?php

namespace Symbiotic\Container;

class ContextualBindingBuilder
{
    /**
     * The underlying container instance.
     *
     * @var DIContainerInterface|ContextualBindingsInterface
     */
    protected $container;

    /**
     * The concrete instance.
     *
     * @var string|array
     */
    protected string|array $concrete;

    /**
     * The abstract target.
     *
     * @var string
     */
    protected string $needs;

    /**
     * Create a new contextual binding builder.
     *
     * @param DIContainerInterface $container
     * @param string|array $concrete
     */
    public function __construct(DIContainerInterface $container, string|array $concrete)
    {
        $this->concrete = $concrete;
        $this->container = $container;
    }

    /**
     * Define the abstract target that depends on the context.
     *
     * @param string $abstract
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
     * @param \Closure|mixed $implementation
     * @return void
     */
    public function give($implementation)
    {
        $concretes = $this->concrete;
        foreach ((!empty($concretes) ? (array)$concretes : []) as $concrete) {
            $this->container->addContextualBinding($concrete, $this->needs, $implementation);
        }
    }
}
