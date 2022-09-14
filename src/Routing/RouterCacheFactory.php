<?php

declare(strict_types=1);

namespace Symbiotic\Routing;

use Psr\SimpleCache\CacheInterface;


class RouterCacheFactory implements RouterFactoryInterface
{

    /**
     * @param RouterFactoryInterface $factory
     * @param CacheInterface|null    $cache
     */
    public function __construct(
        protected RouterFactoryInterface $factory,
        protected ?CacheInterface $cache = null
    ) {
    }

    /**
     * @param array $params
     *
     * @return RouterInterface
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function factoryRouter(array $params = []): RouterInterface
    {
        if ($this->cache) {
            $cache_key = 'router_' . \md5(\serialize($params));

            $data = $this->cache->get($cache_key, $t = \uniqid());
            if ($data === $t) {
                $router = $this->factory->factoryRouter($params);
                $class = $router instanceof LazyRouterInterface ? CacheLazyRouterDecorator::class : CacheRouterDecorator::class;
                $data = new $class($this, $this->factory->factoryRouter($params), $cache_key);
            }

            return $data;
        }

        return $this->factory->factoryRouter($params);
    }

    /**
     * @param RouterInterface $router
     *
     * @return void
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function loadRoutes(RouterInterface $router): void
    {
        $this->factory->loadRoutes($router);
        if ($this->cache && $router instanceof CacheRouterDecorator && $router->isAllowedCache()) {
            $this->cache->set($router->getCacheKey(), $router->getRealInstance());
        }
    }
}
