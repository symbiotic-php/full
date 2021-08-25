<?php

namespace Dissonance\Routing;

use Closure;


class CacheRouterDecorator implements RouterInterface
{

    use AddRouteTrait;

    /**
     * @var RouterFactoryInterface
     */
    protected $factory = null;

    /**
     * @var RouterInterface
     */
    protected $router = null;


    protected $cache_key = '';

    protected $allowed_cache = true;

    public function __construct(RouterFactoryInterface $factory, RouterInterface $router, string $cache_key)
    {
        $this->factory = $factory;
        $this->router = $router;
        $this->cache_key = $cache_key;
    }



    public function getCacheKey(): string
    {
        return $this->cache_key;
    }

    public function isAllowedCache()
    {
        return $this->allowed_cache;
    }

    public function getRealInstance(): RouterInterface
    {
        return $this->router;
    }

    public function setRoutesDomain(string $domain)
    {
        $this->call(__FUNCTION__, func_get_args());
    }


    public function addRoute($httpMethods, string $uri, $action): RouteInterface
    {
        $this->checkCallbacks($action);
        return $this->call(__FUNCTION__, func_get_args());
    }

    private function checkCallbacks($data)
    {
        if (is_array($data)) {
            if (isset($data['middleware'])) {
                foreach ((array)$data['middleware'] as $v) {
                    if ($v instanceof Closure) {
                        $this->allowed_cache = false;
                    }
                }
            }
            if (isset($data['uses']) && $data['uses'] instanceof Closure) {
                $this->allowed_cache = false;
            }
        } elseif ($data instanceof Closure) {
            $this->allowed_cache = false;
        }
    }

    public function group(array $attributes, callable $routes)
    {
        $this->checkCallbacks($attributes);
        $this->router->group($attributes, function ($real_router) use ($routes) {
            $routes($this);
        });
    }

    public function getRoute(string $name): ?RouteInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getBySettlement(string $settlement):array{
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function getRoutes(string $httpMethod = null): array
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function match(string $httpMethod, string $uri): ?RouteInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    protected function call($method, $parameters)
    {
        return call_user_func_array([$this->router, $method], $parameters);
    }


}
