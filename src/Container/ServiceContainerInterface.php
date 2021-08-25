<?php

namespace Dissonance\Container;

interface ServiceContainerInterface
{
    /**
     * Register a service provider with the application.
     *
     * @param ServiceProviderInterface|string $provider
     * @param bool $force
     * @return ServiceProviderInterface
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
     * @param ServiceProviderInterface|string $provider
     * @return ServiceProviderInterface|null
     */
    public function getProvider($provider);

    /**
     * Get the registered service provider instances if any exist.
     *
     * @param ServiceProviderInterface|string $provider
     * @return array
     */
    public function getProviders($provider);

    public function setDeferred(array $services);

    public function isDeferService(string $service):bool;

    public function loadDefer(string $service):bool;
}
