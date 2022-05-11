<?php

namespace Symbiotic\Container;

use Closure;


trait SubContainerTrait /* implements DependencyInjectionInterface, ContextualBindingsInterface*/
{
    use DeepGetterTrait,
        ArrayAccessTrait,
        MethodBindingsTrait,
        ContextualBindingsTrait;

    /**
     * @var DIContainerInterface|null
     */
    protected ?DIContainerInterface $app = null;

    protected array $aliases = [];

    protected array $instances = [];

    protected array $abstractAliases = [];

    protected array $reboundCallbacks = [];

    protected array $bindings = [];

    protected array $resolved = [];

    protected array $extenders = [];


    public function call(callable|string $callback, array $parameters = [], string $defaultMethod = null)
    {
        return BoundMethod::call($this, $callback, $this->bindParameters($parameters), $defaultMethod);
    }

    public function bindParameters(&$parameters)
    {
        $di = DIContainerInterface::class;
        if (!isset($parameters[$di])) {
            $parameters[$di] = $this;
        }

        return $parameters;
    }

    /**
     * @param string $key
     * @return bool
     * @todo: нужно тестировать правильность работы с родительским
     */
    public function has(string $key): bool
    {
        return isset($this->bindings[$key])
            || isset($this->instances[$key])
            || isset($this->aliases[$key])
            || $this->app->has($this->getAlias($key));
    }

    public function getAlias(string $abstract): string
    {
        if (!isset($this->aliases[$abstract])) {
            return $this->app->getAlias($abstract);
        }

        return $this->getAlias($this->aliases[$abstract]);
    }

    public function set($key, $value): void
    {
        $this->bind($key, $value instanceof \Closure ? $value : function () use ($value) {
            return $value;
        });
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
        if (!$concrete) {
            $concrete = $abstract;
        }

        $this->bindings[$abstract] = ['concrete' => function ($container, $parameters = []) use ($abstract, $concrete, $shared) {
            /**
             * @var Container $container
             */
            if ($concrete instanceof \Closure) {
                $instance = $concrete($this, $parameters);
                foreach ($this->getExtenders($abstract) as $v) {
                    $instance = $v($instance);
                }
            } else if ($abstract == $concrete) {
                $container->setContainersStack($this);
                $instance = $container->build($concrete);

                $container->popCurrentContainer();
            } else {
                $instance = $this->app->resolve(
                    $concrete, $parameters, $raiseEvents = false
                );
            }
            $this->resolved[$abstract] = true;
            if ($shared) {
                $this->instances[$abstract] = $instance;
            }
            return $instance;
        }, 'shared' => $shared];

        // If the abstract type was already resolved in this container we'll fire the
        // rebound listener so that any objects which have already gotten resolved
        // can have their copy of the object updated via the listener callbacks.
        $alias = $this->getAlias($abstract);
        if (isset($this->resolved[$alias]) || isset($this->instances[$alias])) {
            $this->rebound($abstract);
        }

    }

    /**
     * Get the extender callbacks for a given type.
     *
     * @param string $abstract
     * @return array
     */
    public function getExtenders(string $abstract)
    {
        return $this->extenders[$this->getAlias($abstract)] ?? [];
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

    public function make(string $abstract, array $parameters = [])
    {
        return $this->resolve($abstract, $parameters);
    }

    public function resolve(string $abstract, array $parameters = [], bool $raiseEvents = true)
    {
        $alias = $this->getAlias($abstract);
        /// сначала получаем по ключу у нас
        /// иначе вернет из ядра по алиасу
        /// проблема одноименнных сервисов с алиасами
        if (!$parameters && isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }
        if (isset($this->bindings[$abstract])) {
            return $this->app->build($this->bindings[$abstract]['concrete']);
        }
        // передаем родителю
        if (!$parameters && isset($this->instances[$alias])) {
            return $this->instances[$alias];
        }
        if (isset($this->bindings[$alias])) {
            return $this->app->build($this->bindings[$alias]['concrete']);
        }

        return $this->app->resolve($alias, $this->bindParameters($parameters), $raiseEvents);
    }

    public function delete(string $key): bool
    {
        unset($this->bindings[$key], $this->instances[$key], $this->resolved[$key], $this->aliases[$key], $this->abstractAliases[$key]);
        return true;
    }

    /**
     * Bind a new callback to an abstract's rebind event.
     *
     * @param string $abstract
     * @param \Closure $callback
     * @return mixed|void
     */
    public function rebinding(string $abstract, Closure $callback)
    {
        $this->reboundCallbacks[$abstract = $this->getAlias($abstract)][] = $callback;

        if ($this->bound($abstract)) {
            return $this->make($abstract);
        }
    }

    public function bound($abstract): bool
    {
        return isset($this->bindings[$abstract])
            || isset($this->instances[$abstract])
            || isset($this->aliases[$abstract])
            || $this->app->bound($abstract);
    }

    public function build(string|Closure $concrete)
    {
        return $this->app->build($concrete);
    }

    public function bindIf(string $abstract, Closure|string $concrete = null, bool $shared = false)
    {
        if (!$this->bound($abstract)) {
            $this->bind($abstract, $concrete, $shared);
        }
    }

    public function singleton(string $abstract, Closure|string $concrete = null, string $alias = null)
    {
        $this->bind($abstract, $concrete, true);
        if (is_string($alias)) {
            $this->alias($abstract, $alias);
        }

        return $this;
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
     * Determine if the given abstract type has been resolved.
     *
     * @param string $abstract
     * @return bool
     */
    public function resolved(string $abstract): bool
    {
        if ($this->isAlias($abstract)) {
            $abstract = $this->getAlias($abstract);
        }

        return isset($this->resolved[$abstract]) ||
            isset($this->instances[$abstract]) || $this->app->resolved($abstract);
    }

    /**
     * Determine if a given string is an alias.
     *
     * @param string $name
     * @return bool
     */
    public function isAlias(string $name): bool
    {
        return isset($this->aliases[$name]) || $this->app->isAlias($name);
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
     * @param string $abstract
     * @param $instance
     * @param string|null $alias
     * @return mixed
     * @todo: перенести в трейт {@see ContainerTrait::instance()} Дубликат!
     *
     */
    public function instance(string $abstract, $instance, string $alias = null)
    {
        if (isset($this->aliases[$abstract])) {
            foreach ($this->abstractAliases as $abstr => $aliases) {
                foreach ($aliases as $index => $als) {
                    if ($als == $abstract) {
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

    /**
     * @param string $concrete
     * @param string $abstract
     * @param $implementation
     *
     * @todo: нужно сделать отдельно от родителя!!
     */
    public function addContextualBinding(string $concrete, string $abstract, $implementation): void
    {
        $this->app->addContextualBinding($concrete, $abstract, $implementation);
    }

    public function when(string|array $concrete): ContextualBindingBuilder
    {
        return $this->app->when($concrete);
    }

    public function factory(string $abstract): Closure
    {
        return function () use ($abstract) {
            return $this->make($abstract);
        };
    }

    public function clear(): void
    {
        $this->aliases = [];
        $this->abstractAliases = [];
    }

    public function resolving($abstract, callable $callback = null)
    {
        if (is_string($abstract)) {
            $abstract = $this->getAlias($abstract);
        }
        // нам не нужен чужой контейнер, ставим текущий
        $this->app->resolving($abstract, function (object $object, $app) use ($callback) {
            return $callback($object, $this);
        });
    }

    /**
     * Register a new after resolving callback for all types.
     *
     * @param \Closure|string $abstract ????
     * @param callable|null $callback
     * @return void
     */
    public function afterResolving($abstract, callable $callback = null)
    {
        if (is_string($abstract)) {
            $abstract = $this->getAlias($abstract);
        }

        // нам не нужен чужой контейнер, ставим текущий
        $this->app->afterResolving($abstract, function (object $object, $app) use ($callback) {
            return $callback($object, $this);
        });
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

}
