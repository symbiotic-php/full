<?php

namespace Symbiotic\Container;


interface ServiceContainerInterface
{
    /**
     * Register a service provider with the application.
     *
     * @param \Symbiotic\Core\ServiceProviderInterface|object|string $provider
     * @param bool $force
     * @return \Symbiotic\Core\ServiceProviderInterface|object
     */
    public function register(object|string $provider, $force = false);

    /**
     * Boot the application's service providers.
     *
     * @return void
     */
    public function boot();


    /**
     * Get the registered service provider instance if it exists.
     *
     * @param \Symbiotic\Core\ServiceProviderInterface|string|object $provider
     * @return \Symbiotic\Core\ServiceProviderInterface|null|object
     */
    public function getProvider(string|object $provider);

    /**
     * Get the registered service provider instances if any exist.
     *
     * @param \Symbiotic\Core\ServiceProviderInterface|object|string $provider
     * @return array
     */
    public function getProviders(string|object $provider): array;

    public function setDeferred(array $services);

    public function isDeferService(string $service): bool;

    public function loadDefer(string $service): bool;
}
