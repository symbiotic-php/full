<?php

declare(strict_types=1);

namespace Symbiotic\Container;

use \Closure;
use Psr\SimpleCache\CacheInterface;


trait CachedContainerTrait /* implements CachedContainerInterface */
{
    /**
     * @var null|CacheInterface
     */
    protected ?CacheInterface $cache = null;

    /**
     * Cache key
     * @var string
     */
    protected string $key = '';
    /**
     * Array of keys of allowed caching services
     * @var array
     */
    protected array $allow_cached = [];

    /**
     * set cache storage
     *
     * @param CacheInterface $cache
     * @param string         $key
     */
    public function setCache(CacheInterface $cache, string $key): void
    {
        $this->cache = $cache;
        $this->key = $key;
    }

    /**
     * @inheritDoc
     *
     * @param string $abstract
     *
     * @return void
     * @see CachedContainerInterface::markAsCached()
     */
    public function markAsCached(string $abstract): void
    {
        $this->allow_cached[$abstract] = 1;
    }

    /**
     * @inheritDoc
     *
     * @param string              $abstract
     * @param Closure|string|null $concrete
     * @param string|null         $alias
     *
     * @return $this
     * @see CachedContainerInterface::cached()
     *
     */
    public function cached(string $abstract, Closure|string $concrete = null, string $alias = null): static
    {
        /**
         * @var $this ContainerTrait|CachedContainerInterface
         */
        // If the service is already from the cache, there is no point in binding
        if (!$this->bound($abstract)) {
            $this->singleton($abstract, $concrete);
        } else {
            if (!$concrete instanceof Closure) {
                $concrete = $this->getClosure($abstract, is_null($concrete) ? $abstract : $concrete);
            }
            $this->bindings[$abstract] = ['concrete' => $concrete, 'shared' => true];
        }
        if ($alias) {
            $this->alias($abstract, $alias);
        }
        $this->allow_cached[$abstract] = 1;

        return $this;
    }

    /**
     * @return array
     */
    protected function getSerializeData(): array
    {
        $data = [
            'cache' => $this->cache,
            'key' => $this->key,
        ];
        $instances = [];
        foreach ($this->allow_cached as $k => $v) {
            if (isset($this->instances[$k])) {
                $instances[$k] = $this->instances[$k];
            }
        }
        $data['instances'] = $instances;

        return $data;
    }

    /**
     * @return string
     *
     * @see \Serializable::serialize()
     */
    public function __serialize(): array
    {
        return $this->getSerializeData();
    }

    /**
     * @inheritDoc
     *
     * @param $serialized
     *
     * @see \Serializable::unserialize()
     */
    public function __unserialize(array $data): void
    {

        $this->cache = $data['cache'];
        $this->key = $data['key'];
        foreach ($data['instances'] as $k => $instance) {
            $this->instances[$k] = $instance;
            $this->resolved[$k] = true;
        }
        $this->unserialized($data);
    }

    /**
     * Prepare after unserialize
     *
     * @param array $data
     */
    protected function unserialized(array $data): void
    {
    }

    public function __destruct()
    {
        /**
         * @var ContainerTrait|SubContainerTrait|$this
         */
        if ($this->cache) {
            if (!$this->cache->has($this->key) && !$this->has('cache_cleaned')) {
                $this->cache->set($this->key, \serialize($this), 60 * 60);
            }
        }
    }
}
