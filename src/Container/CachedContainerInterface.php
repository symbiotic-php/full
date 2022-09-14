<?php

declare(strict_types=1);

namespace Symbiotic\Container;

use Closure;

interface CachedContainerInterface extends DIContainerInterface, \Serializable
{
    /**
     * Allows caching of the service in the container
     *
     * The method works like a singleton, but with caching of the service
     * Only services added via the method {@see DIContainerInterface::singleton()}
     * If there is a cache in the container {@see \Psr\SimpleCache\CacheInterface},
     * then the specified service will be cached
     *
     * @param string              $abstract
     * @param Closure|string|null $concrete
     * @param string|null         $alias
     */
    public function cached(string $abstract, Closure|string $concrete = null, string $alias = null): static;

    /**
     * Marks the service as available for caching
     *
     * @param string $abstract The service key for caching
     *                         (use the interface with which the service was added)
     *
     * @return void
     */
    public function markAsCached(string $abstract): void;
}