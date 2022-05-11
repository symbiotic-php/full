<?php

namespace Symbiotic\Container;


trait ServiceContainerTrait /*implements \Symbiotic\Container\ServiceContainerInterface */
{
    /**
     * @var DIContainerInterface
     */
    protected DIContainerInterface $dependencyInjectionContainer;
    /**
     * All of the registered service providers.
     *
     * @var \Symbiotic\Core\ServiceProvider[]|object[]
     */
    protected array $serviceProviders = [];

    /**
     * The names of the loaded service providers.
     *
     * @var string[]
     */
    protected array $loadedProviders = [];

    /**
     * @var string[]
     */
    protected array $defer_services = [];

    /**
     * Indicates if the application has "booted".
     *
     * @var bool
     */
    protected bool $booted = false;

    public function isDeferService(string $service): bool
    {
        return isset($this->defer_services[\ltrim($service)]);
    }

    /**
     * @param string $service
     * @return bool
     */
    public function loadDefer(string $service): bool
    {
        $class = \ltrim($service);
        if (isset($this->defer_services[$class])) {
            $this->register($this->defer_services[$class]);
            return true;
        }
        return false;
    }

    /**
     * Register a service provider with the application.
     *
     * @param \Symbiotic\Core\ServiceProviderInterface|string|object $provider
     * @param bool $force
     * @return \Symbiotic\Core\ServiceProviderInterface
     */
    public function register(object|string $provider, $force = false)
    {
        /**
         * @var \Symbiotic\Core\ServiceProviderInterface $provider
         */
        if (($registered = $this->getProvider($provider)) && !$force) {
            return $registered;
        }

        // If the given "provider" is a string, we will resolve it, passing in the
        // application instance automatically for the developer. This is simply
        // a more convenient way of specifying your service provider classes.
        if (is_string($provider)) {
            $provider = $this->resolveProvider($provider);
        }

        if (method_exists($provider, 'register')) {
            $provider->register();
        }

        // If there are bindings / singletons set as properties on the provider we
        // will spin through them and register them with the application, which
        // serves as a convenience layer while registering a lot of bindings.
        if (property_exists($provider, 'bindings')) {
            foreach ($provider->bindings() as $key => $value) {
                $this->bind($key, $value);
            }
        }

        if (property_exists($provider, 'singletons')) {
            foreach ($provider->singletons() as $key => $value) {
                $this->singleton($key, $value);
            }
        }
        if (property_exists($provider, 'aliases')) {
            foreach ($provider->aliases() as $key => $value) {
                $this->singleton($key, $value);
            }
        }

        $this->markAsRegistered($provider);

        // If the application has already booted, we will call this boot method on
        // the provider class so it has an opportunity to do its boot logic and
        // will be ready for any usage by this developer's application logic.
        if ($this->booted) {
            $this->bootProvider($provider);
        }

        return $provider;
    }

    /**
     * Get the registered service provider instance if it exists.
     *
     * @param object|string|\Symbiotic\Core\ServiceProviderInterface $provider
     * @return  \Symbiotic\Core\ServiceProviderInterface|null
     */
    public function getProvider(string|object $provider)
    {
        $providers = &$this->serviceProviders;
        $name = $this->getClass($provider);
        return isset($providers[$name]) ? $providers[$name] : null;
    }

    /**
     * @param string |object $provider
     * @return false|string
     */
    protected function getClass(string|object $provider)
    {
        return \is_string($provider) ? \ltrim($provider, '\\') : \get_class($provider);
    }

    /**
     * Resolve a service provider instance from the class name.
     *
     * @param string $provider
     * @return  \Symbiotic\Core\ServiceProviderInterface
     */
    public function resolveProvider(string $provider)
    {
        return new $provider($this->dependencyInjectionContainer);


    }

    /**
     * Mark the given provider as registered.
     *
     * @param \Symbiotic\Core\ServiceProviderInterface|object $provider
     * @return void
     */
    protected function markAsRegistered(object $provider)
    {
        $class = $this->getClass($provider);
        $this->serviceProviders[$class] = $provider;
        $this->loadedProviders[$class] = true;
    }

    /**
     * Boot the given service provider.
     *
     * @param \Symbiotic\Core\ServiceProviderInterface|object $provider
     * @return void
     */
    protected function bootProvider(/*allow use all classes ServiceProviderInterface*/ object $provider)
    {
        if (method_exists($provider, 'boot')) {
            $provider->boot();
        }
    }

    /**
     * @param array $services
     * @used-by \Symbiotic\Core\ProvidersRepository::load()
     */
    public function setDeferred(array $services)
    {
        $this->defer_services = $services;
    }

    /**
     * Boot the application's service providers.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->booted) {
            return;
        }
        array_walk($this->serviceProviders, function ($p) {
            $this->bootProvider($p);
        });

        $this->booted = true;

    }

    /**
     * Get the registered service provider instances if any exist.
     *
     * @param \Symbiotic\Core\ServiceProviderInterface|object|string $provider
     * @return array
     */
    public function getProviders(object|string $provider):array
    {
        $name = $this->getClass($provider);
        return \array_filter($this->serviceProviders, function ($value) use ($name) {
            return $value instanceof $name;
        }, ARRAY_FILTER_USE_BOTH);
    }
}