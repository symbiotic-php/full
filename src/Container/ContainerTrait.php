<?php

namespace Dissonance\Container;

use \Closure;

trait ContainerTrait /* implements DependencyInjectionInterface*/
{
    use ArrayAccessTrait,
        DeepGetterTrait,
        MultipleAccessTrait;

    /**
     * An array of the types that have been resolved.
     *
     * @var bool[]
     */
    protected $resolved = [];

    /**
     * The container's bindings.
     *
     * @var array[]
     */
    protected $bindings = [];


    /**
     * The container's shared instances.
     *
     * @var object[]
     */
    protected $instances = [];

    /**
     * The registered type aliases.
     *
     * @var string[]
     */
    protected $aliases = [];

    /**
     * The registered aliases keyed by the abstract name.
     *
     * @var array[]
     *
     * @used-by alias()
     */
    protected $abstractAliases = [];

    /**
     * The extension closures for services.
     *
     * @var array[]
     */
    protected $extenders = [];

    /**
     * All of the registered tags.
     *
     * @var array[]
     */
    protected $tags = [];

    /**
     * The stack of concretions currently being built.
     *
     * @var array[]
     */
    protected $buildStack = [];

    /**
     * The parameter override stack.
     *
     * @var array[]
     */
    protected $with = [];

    /**
     * The contextual binding map.
     *
     * @var array[]
     */
    public $contextual = [];

    /**
     * @var string |null
     */
    protected $current_build = null;

    /**
     * All of the registered rebound callbacks.
     *
     * @var array[]
     */
    protected $reboundCallbacks = [];

    /**
     * All of the global resolving callbacks.
     *
     * @var \Closure[]
     */
    protected $globalResolvingCallbacks = [];

    /**
     * All of the global after resolving callbacks.
     *
     * @var \Closure[]
     */
    protected $globalAfterResolvingCallbacks = [];

    /**
     * All of the resolving callbacks by class type.
     *
     * @var array[]
     */
    protected $resolvingCallbacks = [];

    /**
     * All of the after resolving callbacks by class type.
     *
     * @var array[]
     */
    protected $afterResolvingCallbacks = [];


    /**
     * @var DIContainerInterface[]
     */
    protected $containersStack = [];


    public function has(string $key): bool
    {
        return $this->bound($key);
    }

    public function set(string $key, $value): void
    {
        $this->bind($key, $value instanceof \Closure ? $value : function () use ($value) {
            return $value;
        });
    }

    public function delete(string $key): bool
    {
        unset($this->bindings[$key], $this->instances[$key], $this->resolved[$key]);
    }

    /**
     * Determine if the given abstract type has been bound.
     *
     * @param string $abstract
     * @return bool
     */
    public function bound(string $abstract)
    {
        return isset($this->bindings[$abstract]) ||
            isset($this->instances[$abstract]) ||
            $this->isAlias($abstract);
    }


    /**
     * Determine if the given abstract type has been resolved.
     *
     * @param string $abstract
     * @return bool
     */
    public function resolved(string $abstract)
    {

        $abstract = $this->getAlias($abstract);

        return isset($this->resolved[$abstract]) ||
            isset($this->instances[$abstract]);
    }

    /**
     * Determine if a given type is shared.
     *
     * @param string $abstract
     * @return bool
     */
    public function isShared(string $abstract)
    {
        return isset($this->instances[$abstract]) ||
            (isset($this->bindings[$abstract]['shared']) &&
                $this->bindings[$abstract]['shared'] === true);
    }

    /**
     * Determine if a given string is an alias.
     *
     * @param string $name
     * @return bool
     */
    public function isAlias(string $name)
    {
        return isset($this->aliases[$name]);
    }

    /**
     * Register a binding with the container.
     *
     * @param string $abstract
     * @param \Closure|string|null $concrete
     * @param bool $shared
     * @return void
     */
    public function bind(string $abstract, Closure|string $concrete = null, bool $shared = false): void
    {
        unset($this->instances[$abstract], $this->aliases[$abstract]);

        // If no concrete type was given, we will simply set the concrete type to the
        // abstract type. After that, the concrete type to be registered as shared
        // without being forced to state their classes in both of the parameters.
        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        // If the factory is not a Closure, it means it is just a class name which is
        // bound into this container to the abstract type and we will just wrap it
        // up inside its own Closure to give us more convenience when extending.
        if (!$concrete instanceof \Closure) {
            $concrete = $this->getClosure($abstract, $concrete);
        }

        $this->bindings[$abstract] = ['concrete' => $concrete, 'shared' => $shared];

        // If the abstract type was already resolved in this container we'll fire the
        // rebound listener so that any objects which have already gotten resolved
        // can have their copy of the object updated via the listener callbacks.
        if ($this->resolved($abstract)) {
            $this->rebound($abstract);
        }
    }

    /*
     *  public function bindClosure(string $abstract, $concrete = null, bool $shared = false)
     {
         $this->bind($abstract, $concrete, $shared);
     }
    */

    /**
     * Get the Closure to be used when building a type.
     *
     * @param string $abstract
     * @param string $concrete
     * @return \Closure
     *
     * @todo protected?
     */
    public function getClosure(string $abstract, string $concrete)
    {
        return function ($container, $parameters = []) use ($abstract, $concrete) {

            /**
             * @var \Dissonance\Container\DIContainerInterface $container
             */
            if ($abstract === $concrete) {
                return $container->build($concrete);
            }
            return $container->resolve(
                $concrete, $parameters, $raiseEvents = false
            );
        };
    }


    /**
     * Register a binding if it hasn't already been registered.
     *
     * @param string $abstract
     * @param \Closure|string|null $concrete
     * @param bool $shared
     * @return $this
     */
    public function bindIf(string $abstract, Closure|string $concrete = null, bool $shared = false)
    {
        if (!$this->bound($abstract)) {
            $this->bind($abstract, $concrete, $shared);
        }

        return $this;
    }

    /**
     * Register a shared binding in the container.
     *
     * @param string $abstract
     * @param \Closure|string|null $concrete
     * @param string|null $alias
     * @return static
     */
    public function singleton(string $abstract, Closure|string $concrete = null, string $alias = null)
    {
        $this->bind($abstract, $concrete, true);
        if (is_string($alias)) {
            $this->alias($abstract, $alias);
        }

        return $this;
    }

    /**
     * "Extend" an abstract type in the container.
     *
     * @param string $abstract
     * @param \Closure $closure
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function extend(string $abstract, Closure $closure): void
    {
        $abstract = $this->getAlias($abstract);

        if (isset($this->instances[$abstract])) {
            $this->instances[$abstract] = $closure($this->instances[$abstract], $this);
            $this->rebound($abstract);
        } else {
            $this->extenders[$abstract][] = $closure;
            if ($this->resolved($abstract)) {
                $this->rebound($abstract);
            }
        }
    }

    /**
     * Register an existing instance as shared in the container.
     *
     * @param string $abstract
     * @param mixed $instance
     * @param null|string $alias
     * @return mixed
     */
    public function instance(string $abstract, $instance, string $alias = null)
    {
        if (isset($this->aliases[$abstract])) {
            foreach ($this->abstractAliases as $abstr => $aliases) {
                foreach ($aliases as $index => $alias) {
                    if ($alias == $abstract) {
                        unset($this->abstractAliases[$abstr][$index]);
                    }
                }
            }
        }
        $isBound = $this->bound($abstract);

        unset($this->aliases[$abstract]);

        // We'll check to determine if this type has been bound before, and if it has
        // we will fire the rebound callbacks registered with the container and it
        // can be updated with consuming classes that have gotten resolved here.
        $this->instances[$abstract] = $instance;

        if ($isBound) {
            $this->rebound($abstract);
        }
        if ($alias) {
            $this->alias($abstract, $alias);
        }

        return $instance;
    }


    public function alias(string $abstract, string $alias)
    {
        if ($alias === $abstract) {
            throw new \LogicException("[{$abstract}] is aliased to itself.");
        }

        $this->aliases[$alias] = $abstract;

        $this->abstractAliases[$abstract][] = $alias;
    }

    /**
     * Get aliases for abstract binding
     *
     * @param string $abstract
     * @return array|null
     */
    public function getAbstractAliases(string $abstract): ?array
    {
        return $this->abstractAliases[$abstract] ?? null;
    }

    /**
     * Bind a new callback to an abstract's rebind event.
     *
     * @param string $abstract
     * @param \Closure $callback
     * @return mixed
     */
    public function rebinding(string $abstract, Closure $callback)
    {
        $this->reboundCallbacks[$abstract = $this->getAlias($abstract)][] = $callback;

        if ($this->bound($abstract)) {
            return $this->make($abstract);
        }
    }

    /**
     * Refresh an instance on the given target and method.
     *
     * @param string $abstract
     * @param mixed $target
     * @param string $method
     * @return mixed
     */
    public function refresh($abstract, $target, $method)
    {
        return $this->rebinding($abstract, function ($app, $instance) use ($target, $method) {
            $target->{$method}($instance);
        });
    }

    /**
     * Fire the "rebound" callbacks for the given abstract type.
     *
     * @param string $abstract
     * @return void
     */
    protected function rebound(string $abstract)
    {
        $instance = $this->make($abstract);

        foreach ((isset($this->reboundCallbacks[$abstract]) ? $this->reboundCallbacks[$abstract] : []) as $callback) {
            call_user_func($callback, $this, $instance);
        }
    }


    /**
     * Wrap the given closure such that its dependencies will be injected when executed.
     *
     * @param \Closure $callback
     * @param array $parameters
     * @param string|null $defaultMethod
     * @return \Closure
     */
    public function wrap(\Closure $callback, array $parameters = [], $defaultMethod = null)
    {
        return function () use ($callback, $parameters, $defaultMethod) {
            return $this->call($callback, $parameters, $defaultMethod);
        };
    }

    /**
     * Call the given Closure / class@method and inject its dependencies.
     *
     * @param callable|string $callback
     * @param array $parameters
     * @param string|null $defaultMethod
     * @return mixed
     */
    public function call(callable|string $callback, array $parameters = [], string $defaultMethod = null)
    {
        return BoundMethod::call($this, $callback, $parameters, $defaultMethod);
    }

    /**
     * Get a closure to resolve the given type from the container.
     *
     * @param string $abstract
     * @return \Closure
     */
    public function factory(string $abstract): Closure
    {
        return function () use ($abstract) {
            return $this->make($abstract);
        };
    }


    /**
     * Resolve the given type from the container.
     *
     * @param string $abstract
     * @param array $parameters
     * @return mixed
     *
     */
    public function make(string $abstract, array $parameters = [])
    {
        return $this->resolve($abstract, $parameters);
    }


    public function setContainersStack(DIContainerInterface $container)
    {
        $this->containersStack[] = $container;
    }

    public function popCurrentContainer()
    {
        array_pop($this->containersStack);
    }

    /**
     * Resolve the given type from the container.
     *
     * @param string $abstract
     * @param array $parameters
     * @param bool $raiseEvents
     * @return mixed
     *
     * @throws BindingResolutionException
     */
    public function resolve(string $abstract, array $parameters = [], bool $raiseEvents = true)
    {
        if (empty($parameters)) {
            $container = !empty($this->containersStack) ? end($this->containersStack) : null;
            if ($container && $container instanceof $abstract) {
                return $container;
            }
        }

        $abstract = $this->getAlias($abstract);
        $interface = DIContainerInterface::class;
        if (isset($parameters[$interface])) {
            if ($abstract === $interface) {
                return $parameters[$interface];
            }
            $this->containersStack[] = $parameters[$interface];
            unset($parameters[$interface]);
        } else {
            $this->containersStack[] = $this;
        }


        // todo: test current_build var
        $conceptual_concrete = $this->current_build ? $this->getContextualConcrete($this->current_build, $abstract) : null;


        $needsContextualBuild = !empty($parameters) || null !== $conceptual_concrete;

        // If an instance of the type is currently being managed as a singleton we'll
        // just return an existing instance instead of instantiating new instances
        // so the developer can keep using the same objects instance every time.
        if (!$needsContextualBuild) {
            if (isset($this->instances[$abstract])) {
                return $this->instances[$abstract];
            }
        }


        $this->with[] = $parameters;

        $concrete = !empty($conceptual_concrete) ?
            $conceptual_concrete :
            (isset($this->bindings[$abstract])
                ? $this->bindings[$abstract]['concrete'] :
                ((($this instanceof ServiceContainerInterface
                        && $this->loadDefer($abstract))
                    && isset($this->bindings[$this->getAlias($abstract)])
                ) ? $this->bindings[$this->getAlias($abstract)/*todo: два раза дергаем*/]['concrete'] :
                    $abstract)
            );


        // We're ready to instantiate an instance of the concrete type registered for
        // the binding. This will instantiate the types, as well as resolve any of
        // its "nested" dependencies recursively until all have gotten resolved.
        if ($this->isBuildable($concrete, $abstract)) {
            if(\is_string($concrete) && \strpos($concrete,'\\')===false) {
                throw new NotFoundException($concrete,$this);
            }
            $object = $this->build($concrete);
        } else {
            $object = $this->make($concrete);
        }

        // If we defined any extenders for this type, we'll need to spin through them
        // and apply them to the object being built. This allows for the extension
        // of services, such as changing configuration or decorating the object.
        foreach ($this->getExtenders($abstract) as $extender) {
            $object = $extender($object, $this);
        }

        // If the requested type is registered as a singleton we'll want to cache off
        // the instances in "memory" so we can return it later without creating an
        // entirely new instance of an object on each subsequent request for it.
        if ($this->isShared($abstract) && !$needsContextualBuild) {
            $this->instances[$abstract] = $object;
        }

        if ($raiseEvents) {
            $this->fireResolvingCallbacks($abstract, $object);
        }

        // Before returning, we will also set the resolved flag to "true" and pop off
        // the parameter overrides for this build. After those two things are done
        // we will be ready to return back the fully constructed class instance.
        $this->resolved[$abstract] = true;

        array_pop($this->with);

        return $object;
    }


    /**
     * Get the contextual concrete binding for the given abstract.
     *
     * @param string $for_building
     * @param string $need The name of the class ('\MySpace\ClassName') or variable ('$var_name') to build the dependency on.
     * @return \Closure|mixed|null
     */
    protected function getContextualConcrete(string $for_building, string $need)
    {
        $current_container = end($this->containersStack);
        return ($current_container instanceof ContextualBindingsInterface) ? $current_container->getContextualConcrete($for_building, $need) : null;
    }

    /**
     * Determine if the given concrete is buildable.
     *
     * @param string|\Closure $concrete
     * @param string $abstract
     * @return bool
     */
    protected function isBuildable(string|\Closure $concrete, string $abstract)
    {
        return $concrete === $abstract || $concrete instanceof \Closure;
    }

    /**
     * Instantiate a concrete instance of the given type.
     *
     * @param string $concrete
     * @return mixed
     *
     * @throws BindingResolutionException|ContainerException
     */
    public function build(string|Closure $concrete)
    {
        // If the concrete type is actually a Closure, we will just execute it and
        // hand back the results of the functions, which allows functions to be
        // used as resolvers for more fine-tuned resolution of these objects.
        if ($concrete instanceof \Closure) {
            return $concrete($this, $this->getLastParameterOverride());
        }

        try {
            $reflector = new \ReflectionClass($concrete);
        } catch (\Exception $e) {
            throw new ContainerException("Target [$concrete] is not instantiable and key not exists in container data!");
        }


        // If the type is not instantiable, the developer is attempting to resolve
        // an abstract type such as an Interface or Abstract Class and there is
        // no binding registered for the abstractions so we need to bail out.
        if (!$reflector->isInstantiable()) {
            if (!empty($this->buildStack)) {
                $previous = implode(', ', $this->buildStack);
                $message = "Target [$concrete] is not instantiable while building [$previous].";
            } else {
                $message = "Target [$concrete] is not instantiable.";
            }
            throw new ContainerException($message);
        }

        $this->buildStack[] = $concrete;
        $this->current_build = $concrete;
        $constructor = $reflector->getConstructor();

        // If there are no constructors, that means there are no dependencies then
        // we can just resolve the instances of the objects right away, without
        // resolving any other types or dependencies out of these containers.
        if (null === $constructor) {
            array_pop($this->buildStack);
            $this->current_build = end($this->buildStack);
            return new $concrete;
        }

        $dependencies = $constructor->getParameters();

        // Once we have all the constructor's parameters we can create each of the
        // dependency instances and then use the reflection instances to make a
        // new instance of this class, injecting the created dependencies in.
        $instances = $this->resolveDependencies(
            $dependencies
        );

        array_pop($this->buildStack);
        $this->current_build = end($this->buildStack);
        return $reflector->newInstanceArgs($instances);
    }

    /**
     * Resolve all of the dependencies from the ReflectionParameters.
     *
     * @param array|\ReflectionParameter[] $dependencies
     * @return array
     *
     * @throws BindingResolutionException
     */
    protected function resolveDependencies(array $dependencies)
    {
        $results = [];

        foreach ($dependencies as $k => $dependency) {
            // If this dependency has a override for this particular build we will use
            // that instead as the value. Otherwise, we will continue with this run
            // of resolutions and let reflection attempt to determine the result.
            if ($this->hasParameterOverride($dependency, $k)) {
                $results[] = $this->getParameterOverride($dependency, $k);

                continue;
            }

            // If the class is null, it means the dependency is a string or some other
            // primitive type which we can not resolve since it is not a class and
            // we will just bomb out with an error since we have no-where to go.
            $results[] = is_null(Reflection::getParameterClassName($dependency))
                ? $this->resolvePrimitive($dependency)
                : $this->resolveClass($dependency);
        }

        return $results;
    }

    /**
     * Determine if the given dependency has a parameter override.
     *
     * @param \ReflectionParameter $dependency
     * @param int|null $param_number
     * @return bool
     */
    protected function hasParameterOverride(\ReflectionParameter $dependency, int $param_number = null)
    {
        $params = $this->getLastParameterOverride();
        return (array_key_exists($dependency->name, $params)
            || (null !== $param_number && array_key_exists($param_number, $params)));


    }

    /**
     * Get a parameter override for a dependency.
     *
     * @param \ReflectionParameter $dependency
     * @param int|null $param_number
     * @return mixed
     */
    protected function getParameterOverride(\ReflectionParameter $dependency, $param_number = null)
    {
        $params = $this->getLastParameterOverride();
        if (array_key_exists($dependency->name, $params)) {
            return $params[$dependency->name];
        } elseif (null !== $param_number && array_key_exists($param_number, $params)) {
            return $params[$param_number];
        } elseif (($class = Reflection::getParameterClassName($dependency)) && array_key_exists($class, $params)) {
            return $params[$class];
        } /*elseif (null !== $param_number && array_key_exists($param_number, $value_params)) {
            return $value_params[$param_number];
        }*/
        return null;
    }

    /**
     * Get the last parameter override.
     *
     * @return array
     */
    protected function getLastParameterOverride()
    {
        return !empty($this->with) ? end($this->with) : [];
    }

    /**
     * Resolve a non-class hinted primitive dependency.
     *
     * @param \ReflectionParameter $parameter
     * @return mixed
     *
     * @throws BindingResolutionException
     */
    protected function resolvePrimitive(\ReflectionParameter $parameter)
    {
        if ($this->current_build && !is_null($concrete = $this->getContextualConcrete($this->current_build, '$' . $parameter->name))) {
            return $concrete instanceof \Closure ? $concrete($this) : $concrete;
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }
        throw new \ArgumentCountError("Unresolvable dependency resolving [$parameter] in class {$parameter->getDeclaringClass()->getName()}::{$parameter->getDeclaringFunction()->getName()}");
    }

    /**
     * Resolve a class based dependency from the container.
     *
     * @param \ReflectionParameter $parameter
     * @return mixed
     *
     * @throws BindingResolutionException
     */
    protected function resolveClass(\ReflectionParameter $parameter)
    {
        try {
            $container = end($this->containersStack);
            $class = Reflection::getParameterClassName($parameter);
            return $container ? $container->make($class) : $this->make($class);
        }

            // If we can not resolve the class instance, we will check to see if the value
            // is optional, and if it is we will return the optional parameter value as
            // the value of the dependency, similarly to how we do this with scalars.
        catch (BindingResolutionException $e) {
            if ($parameter->isOptional()) {
                return $parameter->getDefaultValue();
            }

            throw $e;
        }
    }


    /**
     * Register a new resolving callback.
     *
     * @param \Closure|string $abstract
     * @param callable|null $callback closure or Invokable object
     * @return void
     */
    public function resolving(string|\Closure $abstract, callable $callback = null)
    {
        if (is_string($abstract)) {
            $abstract = $this->getAlias($abstract);
        }

        if (is_null($callback) && is_callable($abstract)) {
            $this->globalResolvingCallbacks[] = $abstract;
        } else {
            $this->resolvingCallbacks[$abstract][] = $callback;
        }
    }

    /**
     * Register a new after resolving callback for all types.
     *
     * @param \Closure|string $abstract
     * @param callable|null $callback closure or Invokable object
     * @return void
     */
    public function afterResolving(\Closure|string $abstract, callable $callback = null)
    {
        if (is_string($abstract)) {
            $abstract = $this->getAlias($abstract);
        }

        if (is_callable($abstract) && is_null($callback)) {
            $this->globalAfterResolvingCallbacks[] = $abstract;
        } else {
            $this->afterResolvingCallbacks[$abstract][] = $callback;
        }
    }

    /**
     * Fire all of the resolving callbacks.
     *
     * @param string $abstract
     * @param mixed $object
     * @return void
     */
    protected function fireResolvingCallbacks(string $abstract, $object)
    {

        $this->fireResolvingByData($abstract, $object, $this->globalResolvingCallbacks, $this->resolvingCallbacks);
        $this->fireResolvingByData($abstract, $object, $this->globalAfterResolvingCallbacks, $this->afterResolvingCallbacks);

    }

    protected function fireResolvingByData(string $abstract, $object, array $global_callbacks = [], array $types_callbacks = [])
    {
        if (!empty($global_callbacks)) {
            $this->fireCallbackArray($object, $global_callbacks);
        }

        $callbacks = $this->getCallbacksForType($abstract, $object, $types_callbacks);
        if (!empty($callbacks)) {
            $this->fireCallbackArray($object, $callbacks);
        }
    }

    /**
     * Get all callbacks for a given type.
     *
     * @param string $abstract
     * @param mixed $value
     * @param array $callbacksPerType
     *
     * @return array
     */
    protected function getCallbacksForType(string $abstract, $value, array $callbacksPerType)
    {
        $results = [];

        foreach ($callbacksPerType as $type => $callbacks) {
            if ($type === $abstract || (is_object($value) && $value instanceof $type)) {
                $results = array_merge($results, $callbacks);
            }
        }

        return $results;
    }

    /**
     * Fire an array of callbacks with an object.
     *
     * @param mixed $object
     * @param array $callbacks
     * @return void
     */
    protected function fireCallbackArray($object, array $callbacks)
    {
        foreach ($callbacks as $callback) {
            $callback($object, $this);
        }
    }

    /**
     * Get the container's bindings.
     *
     * @return array
     */
    public function getBindings()
    {
        return $this->bindings;
    }

    /**
     * Get the alias for an abstract if available.
     *
     * @param string $abstract
     * @return string
     */
    public function getAlias(string $abstract): string
    {

        /*  if (!is_string($abstract) || !isset($this->aliases[$abstract])) {
              return $abstract;
          }*/
        while (isset($this->aliases[$abstract])) {
            $abstract = $this->aliases[$abstract];
        }
        return $abstract;
        //return $this->getAlias($this->aliases[$abstract]);
    }

    /**
     * Get the extender callbacks for a given type.
     *
     * @param string $abstract
     * @return array
     */
    public function getExtenders(string $abstract)
    {
        $container = !empty($this->containersStack) ? end($this->containersStack) : null;

       // return \spl_object_id($container) === \spl_object_id($this)
        return $container instanceof $this
            ? $this->extenders[$this->getAlias($abstract)] ?? []
            : $container->getExtenders($abstract);

    }

    /**
     * Remove all of the extender callbacks for a given type.
     *
     * @param string $abstract
     * @return void
     */
    public function forgetExtenders(string $abstract)
    {
        unset($this->extenders[$this->getAlias($abstract)]);
    }

    /**
     * Drop all of the stale instances and aliases.
     *
     * @param string $abstract
     * @return void
     */
    protected function dropStaleInstances($abstract)
    {
        unset($this->instances[$abstract], $this->aliases[$abstract]);
    }

    /**
     * Remove a resolved instance from the instance cache.
     *
     * @param string $abstract
     * @return void
     */
    public function forgetInstance($abstract)
    {
        unset($this->instances[$abstract]);
    }

    /**
     * Clear all of the instances from the container.
     *
     * @return void
     */
    public function forgetInstances()
    {
        $this->instances = [];
    }

    /**
     * Flush the container of all bindings and resolved instances.
     *
     * @return void
     */
    public function clear(): void
    {
        $this->aliases = [];
        $this->resolved = [];
        $this->bindings = [];
        $this->instances = [];
        $this->abstractAliases = [];
    }

}