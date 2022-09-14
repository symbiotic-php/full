<?php

declare(strict_types=1);

namespace Symbiotic\Routing;

use Closure;


class CacheRouterDecorator implements RouterInterface
{
    use AddRouteTrait;

    /**
     * @var bool
     */
    protected bool $allowed_cache = true;


    /**
     * @param RouterFactoryInterface $factory
     * @param RouterInterface        $router
     * @param string                 $cache_key
     */
    public function __construct(
        protected RouterFactoryInterface $factory,
        protected RouterInterface $router,
        protected string $cache_key
    ) {
    }


    /**
     * @return string
     */
    public function getCacheKey(): string
    {
        return $this->cache_key;
    }

    /**
     * @return bool
     */
    public function isAllowedCache(): bool
    {
        return $this->allowed_cache;
    }

    /**
     * @return RouterInterface
     */
    public function getRealInstance(): RouterInterface
    {
        return $this->router;
    }

    /**
     * @param array $params
     *
     * @return void
     */
    public function setParams(array $params): void
    {
        $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * @param string $domain
     *
     * @return void
     */
    public function setRoutesDomain(string $domain): void
    {
        $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    protected function call(string $method, array $parameters): mixed
    {
        return call_user_func_array([$this->router, $method], $parameters);
    }

    /**
     * @param string|array         $httpMethods
     * @param string               $uri
     * @param string|array|Closure $action
     *
     * @return RouteInterface
     */
    public function addRoute(string|array $httpMethods, string $uri, string|array|Closure $action): RouteInterface
    {
        $this->checkCallbacks($action);
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * @param $data
     *
     * @return void
     */
    private function checkCallbacks($data): void
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

    /**
     * @param array   $attributes
     * @param Closure $routes
     *
     * @return void
     */
    public function group(array $attributes, Closure $routes): void
    {
        $this->checkCallbacks($attributes);
        $this->router->group($attributes, function ($real_router) use ($routes) {
            $routes($this);
        });
    }

    /**
     * @param string $name
     *
     * @return RouteInterface|null
     */
    public function getByName(string $name): ?RouteInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * @return array|RouteInterface[]
     */
    public function getNamedRoutes(): array
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * @param string $name
     *
     * @return array|RouteInterface[]
     */
    public function getByNamePrefix(string $name): array
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * @param string|null $httpMethod
     *
     * @return array
     */
    public function getRoutes(string $httpMethod = null): array
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * @param string $httpMethod
     * @param string $uri
     *
     * @return RouteInterface|null
     */
    public function match(string $httpMethod, string $uri): ?RouteInterface
    {
        return $this->call(__FUNCTION__, func_get_args());
    }
}
