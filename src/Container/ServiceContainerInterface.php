<?php

namespace Symbiotic\Container;


interface ServiceContainerInterface
{
    /**
     * Register a service provider with the application.
     *
     * @param  \Symbiotic\Core\ServiceProviderInterface|string $provider
     * @param bool $force
     * @return \Symbiotic\Core\ServiceProviderInterface
     */
    public function register($provider, $force = false);

    /**
     * Boot the application's service providers.
     *
     * @return void
     */
    public function boot();


    /**
     * Get the registered service provider instance if it exists.
     *
     * @param \Symbiotic\Core\ServiceProviderInterface|string $provider
     * @return \Symbiotic\Core\ServiceProviderInterface|null
     */
    public function getProvider($provider);

    /**
     * Get the registered service provider instances if any exist.
     *
     * @param \Symbiotic\Core\ServiceProviderInterface|string $provider
     * @return array
     */
    public function getProviders($provider);

    public function setDeferred(array $services);

    public function isDeferService(string $service):bool;

    public function loadDefer(string $service):bool;
}
