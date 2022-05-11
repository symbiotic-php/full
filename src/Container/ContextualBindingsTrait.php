<?php

namespace Symbiotic\Container;

/**
 * Trait ConceptualBindingsTrait
 * @package Symbiotic\Container
 *
 * Use only in @see DIContainerInterface
 */
trait ContextualBindingsTrait /*implements ContextualBindingsInterface*/
{
    /**
     * The contextual binding map.
     *
     * @var string[][]
     */
    public array $contextual = [];

    /**
     * Define a contextual binding.
     *
     * @param array|string $concrete
     * @return ContextualBindingBuilder
     */
    public function when(string|array $concrete): ContextualBindingBuilder
    {
        /**
         * @var DIContainerInterface $this
         */
        $aliases = [];
        $concrete = is_array($concrete) ? $concrete : [$concrete];
        foreach ($concrete as $c) {
            $aliases[] = $this->getAlias($c);
        }

        return new ContextualBindingBuilder($this, $aliases);
    }

    /**
     * Add a contextual binding to the container.
     *
     * @param string $concrete
     * @param string $abstract
     * @param mixed $implementation
     * @return void
     */
    public function addContextualBinding(string $concrete, string $abstract, $implementation): void
    {
        /**
         * @var DIContainerInterface|ContextualBindingsInterface|ContextualBindingsTrait $this
         */
        $this->contextual[$concrete][$this->getAlias($abstract)] = $implementation;
    }

    /**
     * Get the contextual concrete binding for the given abstract.
     *
     * @param string $for_building
     * @param string $need
     *
     * @return \Closure|mixed|null
     */
    public function getContextualConcrete(string $for_building, string $need)
    {

        /**
         * @var DIContainerInterface|ContextualBindingsInterface $this
         */
        if (isset($this->contextual[$for_building][$need])) {
            return $this->contextual[$for_building][$need];
        }
        $aliases = $this->getAbstractAliases($need);
        // Next we need to see if a contextual binding might be bound under an alias of the
        // given abstract type. So, we will need to check if any aliases exist with this
        // type and then spin through them and check for contextual bindings on these.
        if (empty($aliases)) {
            return null;
        }

        foreach ($aliases as $alias) {
            if (isset($this->contextual[$for_building][$alias])) {
                return $this->contextual[$for_building][$alias];
            }
        }
        return null;
    }
}