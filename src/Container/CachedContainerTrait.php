<?php

namespace Symbiotic\Container;

use \Closure;
use Psr\SimpleCache\CacheInterface;

/**
 * Trait CacheContainerTrait
 * @package Symbiotic\Container
 */
trait CachedContainerTrait /*implements CachedContainerInterface*/
{
    /**
     * @var null|CacheInterface
     */
    protected ?CacheInterface $container_cache = null;

    protected string $container_key = '';
    /**
     * Массив ключей разрешенных сервисов для кеширования
     * @var array|string[]
     */
    protected array $allow_cached = [];

    /**
     * set cache storage
     * @param CacheInterface $cache
     * @param string $key
     */
    public function setCache(CacheInterface $cache, string $key)
    {
        $this->container_cache = $cache;
        $this->container_key = $key;
    }

    public function addToCache(string $abstract)
    {
        $this->allow_cached[$abstract] = 1;
    }

    /**
     * Разрешает кеширование сервиса в контейнере
     *
     * Если есть сервис кеша {@see \Psr\SimpleCache\CacheInterface} в контейнере , то указанный ключ будет добавлен для кешироваиня
     *
     * @param string $abstract - ключ сервиса для кеширования
     * @param Closure|string|null $concrete
     * @param string|null $alias
     */
    public function cached(string $abstract, Closure|string $concrete = null, string $alias = null)
    {

        /**
         * @var $this ContainerTrait|CachedContainerInterface
         */
        // Если сервис есть уже из кеша, нет смысла биндить
        if (!$this->bound($abstract)) {
            $this->singleton($abstract, $concrete);
        } else {
            if (!$concrete instanceof \Closure) {
                $concrete = $this->getClosure($abstract, is_null($concrete) ? $abstract : $concrete);
            }
            $this->bindings[$abstract] = ['concrete' => $concrete, 'shared' => true];
            //$this->fireResolvingCallbacks($abstract, $this->instances[$abstract]);
        }
        if ($alias) {
            $this->alias($abstract, $alias);
        }
        $this->allow_cached[$abstract] = 1;
    }

    /**
     * @return string
     *
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
        return \serialize($this->getSerializeData());
    }

    protected function getSerializeData(): array
    {
        /**
         * @var \Symbiotic\Container\ContainerTrait|\Symbiotic\Container\SubContainerTrait| $this
         */
        $data = [
            'cache' => $this->container_cache,
            'key' => $this->container_key,
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
     * @param $serialized
     *
     * @see \Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        /**
         * @var \Symbiotic\Container\ContainerTrait|\Symbiotic\Container\SubContainerTrait| $this
         */
        $data = \unserialize($serialized, ['allowed_classes' => true]);
        $this->container_cache = $data['cache'];
        $this->container_key = $data['key'];
        foreach ($data['instances'] as $k => $instance) {
            $this->instances[$k] = $instance;
            $this->resolved[$k] = true;
        }
        $this->unserialized($data);
    }

    protected function unserialized(array $data)
    {

    }

    public function __destruct()
    {
        /**
         * @var \Symbiotic\Container\ContainerTrait|\Symbiotic\Container\SubContainerTrait| $this
         */
        if ($this->container_cache) {
            if (!$this->container_cache->has($this->container_key) && !$this->has('cache_cleaned')) {
                $this->container_cache->set($this->container_key, $this, 60 * 60);
            }
        }


    }

}
