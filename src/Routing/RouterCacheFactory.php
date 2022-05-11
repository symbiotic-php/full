<?php

namespace Symbiotic\Routing;

use Psr\SimpleCache\CacheInterface;



class RouterCacheFactory implements RouterFactoryInterface
{

    /**
     * @var RouterFactoryInterface
     */
    protected RouterFactoryInterface |null $factory = null;

    /**
     * @var CacheInterface|null
     */
    protected CacheInterface|null $cache = null;

    public function __construct(RouterFactoryInterface $factory, CacheInterface $cache = null)
    {
        $this->factory = $factory;
        $this->cache = $cache;
    }

    public function factoryRouter(array $params = []): RouterInterface
    {
        if ($this->cache) {
            $cache_key = 'router_' . \md5(\serialize($params));

            $data = $this->cache->get($cache_key, $t = \uniqid());
            if ($data === $t) {
                $router = $this->factory->factoryRouter($params);
                $class = $router instanceof LazyRouterInterface?CacheLazyRouterDecorator::class :CacheRouterDecorator::class;
                $data = new $class($this, $this->factory->factoryRouter($params), $cache_key);
            }

            return $data;
        }

        return $this->factory->factoryRouter($params);
    }

    public function loadRoutes(RouterInterface $router)
    {
        $this->factory->loadRoutes($router);
        if ($this->cache && $router instanceof CacheRouterDecorator && $router->isAllowedCache()) {
            $this->cache->set($router->getCacheKey(), $router->getRealInstance());
        }
    }
}
