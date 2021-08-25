<?php

namespace Dissonance\Container;

use \Closure;
use Psr\Container\ContainerInterface;

/**
 * Interface DependencyInjectionInterface
 * @package Dissonance\Container
 */
interface DIContainerInterface extends ArrayContainerInterface, ContainerInterface, FactoryInterface
{

    /**
     * Determine if the given abstract type has been bound.
     *
     * @param string $abstract
     * @return bool
     */
    public function bound(string $abstract);

    /**
     * Alias a type to a different name.
     *
     * @param string $abstract
     * @param string $alias
     * @return void
     *
     * @throws \LogicException
     */
    public function alias(string $abstract, string $alias);


    /**
     * Register a binding with the container.
     *
     * @param string $abstract
     * @param Closure|string|null $concrete
     * @param bool $shared
     * @return void
     */
    public function bind(string $abstract,  $concrete = null, bool $shared = false): void;

    /**
     * Bind a new callback to an abstract's rebind event.
     *
     * @param string $abstract
     * @param Closure $callback
     * @return mixed
     */
    public function rebinding(string $abstract, Closure $callback);

    /**
     * Register a binding if it hasn't already been registered.
     *
     * @param string $abstract
     * @param Closure|string|null $concrete
     * @param bool $shared
     * @return void
     */
    public function bindIf(string $abstract,  $concrete = null, bool $shared = false);

    /**
     * Register a shared binding in the container.
     *
     * @param string $abstract
     * @param Closure|string|null $concrete
     * @param string|null $alias
     * @return static
     */
    public function singleton(string $abstract,  $concrete = null, string $alias = null);

    /**
     * "Extend" an abstract type in the container.
     *
     * @param string $abstract
     * @param Closure $closure
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function extend(string $abstract, Closure $closure): void;

    /**
     * Register an existing instance as shared in the container.
     *
     * @param string $abstract
     * @param mixed $instance
     * @param null|string $alias
     * @return mixed
     */
    public function instance(string $abstract, $instance, string $alias = null);

    /**
     * Resolve the given type from the container.
     *
     * @param string $abstract
     * @param array $parameters
     * @param bool $raiseEvents
     * @return mixed
     *
     * @throws BindingResolutionException;
     */
    public function resolve(string $abstract, array $parameters = [], bool $raiseEvents = true);

    /**
     * @param Closure|string $concrete string class name or closure factory
     * @return mixed
     * @example function(DependencyInjectionInterface $container, array $params = []){.... return $object;}
     */
    public function build($concrete);


    /**
     * Get a closure to resolve the given type from the container.
     *
     * @param string $abstract
     * @return Closure
     */
    public function factory(string $abstract): Closure;

    /**
     * Flush the container of all bindings and resolved instances.
     *
     * @return void
     */
    public function clear():void;

    /**
     * Call the given Closure or 'className@methodName' and inject its dependencies.
     *
     * @param callable|string $callback
     * @param array $parameters
     * @param string|null $defaultMethod
     * @return mixed
     */
    public function call($callback, array $parameters = [], string $defaultMethod = null);

    /**
     * Determine if the given abstract type has been resolved.
     *
     * @param string $abstract
     * @return bool
     */
    public function resolved(string $abstract);

    /**
     * Register a new resolving callback.
     *
     * @param Closure|string $abstract if closure set to global resolving event
     * @param callable|null $callback
     * @return void
     */
    public function resolving($abstract, callable $callback = null);

    /**
     * Register a new after resolving callback.
     *
     * @param Closure|string $abstract
     * @param callable|null $callback
     * @return void
     */
    public function afterResolving($abstract, callable $callback = null);

    /**
     * Determine if a given string is an alias.
     *
     * @param string $name
     * @return bool
     */
    public function isAlias(string $name);

    /**
     * Get aliases for abstract binding
     *
     * @param string $abstract
     * @return array|null
     */
    public function getAbstractAliases(string $abstract): ?array;

    /**
     * Get the alias for an abstract if available.
     *
     * @param string $abstract
     * @return string
     */
    public function getAlias(string $abstract):string;


    /**
     * Special get method with default
     *
     * @param $key
     * @param null $default
     * @return mixed
     */
    public function __invoke($key, $default = null);
}
