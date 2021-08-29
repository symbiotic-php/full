<?php

namespace Symbiotic\Container;


trait ServiceContainerTrait /*implements \Symbiotic\Container\ServiceContainerInterface */
{
    /**
     * @var DIContainerInterface
     */
    protected $dependencyInjectionContainer;
    /**
     * All of the registered service providers.
     *
     * @var \Symbiotic\Core\ServiceProvider[]
     */
    protected array $serviceProviders = [];

    /**
     * The names of the loaded service providers.
     *
     * @var array
     */
    protected array  $loadedProviders = [];

    protected array $defer_services = [];

    /**
     * Indicates if the application has "booted".
     *
     * @var bool
     */
    protected $booted = false;

    public function isDeferService(string $service):bool
    {
        return isset($this->defer_services[\ltrim($service)]);
    }

    public function loadDefer(string $service):bool
    {
        $class = \ltrim($service);
        if(isset($this->defer_services[$class])) {
            $this->register($this->defer_services[$class]);
            return true;
        }
        return false;
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
     * Register a service provider with the application.
     *
     * @param  \Symbiotic\Core\ServiceProviderInterface|string $provider
     * @param bool $force
     * @return \Symbiotic\Core\ServiceProviderInterface
     */
    public function register($provider, $force = false)
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
     * Boot the given service provider.
     *
     * @param  \Symbiotic\Core\ServiceProviderInterface $provider
     * @return mixed
     */
    protected function bootProvider(/*allow use all classes ServiceProviderInterface*/ $provider)
    {
        if (method_exists($provider, 'boot')) {
            return $provider->boot();
        }
    }


    /**
     * Resolve a service provider instance from the class name.
     *
     * @param string $provider
     * @return  \Symbiotic\Core\ServiceProviderInterface
     */
    public function resolveProvider($provider)
    {
        return new $provider($this->dependencyInjectionContainer);
    }


    /**
     * Mark the given provider as registered.
     *
     * @param  \Symbiotic\Core\ServiceProviderInterface $provider
     * @return void
     */
    protected function markAsRegistered($provider)
    {
        $class = $this->getClass($provider);
        $this->serviceProviders[$class] = $provider;
        $this->loadedProviders[$class] = true;
    }

    /**
     * Get the registered service provider instance if it exists.
     *
     * @param  \Symbiotic\Core\ServiceProviderInterface|string $provider
     * @return  \Symbiotic\Core\ServiceProviderInterface|null
     */
    public function getProvider($provider)
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
        return  \is_string($provider) ? \ltrim($provider, '\\') : \get_class($provider);
    }

    /**
     * Get the registered service provider instances if any exist.
     *
     * @param  \Symbiotic\Core\ServiceProviderInterface|string $provider
     * @return array
     */
    public function getProviders($provider)
    {
        $name = $this->getClass($provider);
        return \array_filter($this->serviceProviders, function ($value) use ($name) {
            return $value instanceof $name;
        }, ARRAY_FILTER_USE_BOTH);
    }
}