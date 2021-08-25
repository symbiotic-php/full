<?php

namespace Dissonance\Container;

interface ContextualBindingsInterface
{
    /**
     * Define a contextual binding.
     *
     * @param array|string $concrete
     *
     * @return ContextualBindingBuilder
     */
    public function when(string|array $concrete): ContextualBindingBuilder;


    /**
     * Add a contextual binding to the container.
     *
     * @param string $concrete
     * @param string $abstract
     * @param mixed $implementation
     * @return void
     */
    public function addContextualBinding(string $concrete, string $abstract, $implementation): void;

    /**
     * Get the contextual concrete binding for the given abstract.
     *
     * @param string $for_building
     *
     * @param string $need
     *
     * @return \Closure|mixed|null
     */
    public function getContextualConcrete(string $for_building, string $need);
}
