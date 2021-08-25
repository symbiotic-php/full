<?php

namespace Dissonance\Routing;


use Dissonance\Core\Support\Collection;

class SettlementsRouter extends Router implements RouterInterface
{
    const DELIMITER = '::';

    const DEFAULT_ROUTER = 'default';

    /**
     * @var RouterLazy[]|\Dissonance\Routing\RouterInterface[]
     */
    protected $routers = [];

    /**
     * @var Router|\Dissonance\Routing\RouterInterface|null
     */
    protected $current_router = null;

    /**
     * @var string
     */
    protected $current_router_name = null;

    protected $previous_collections_names = [];

    /**
     * @var Settlements|Settlement[]
     */
    protected $settlements = null;


    protected $router_factory = null;

    public function __construct(RouterFactoryInterface $routerFactory, SettlementsInterface $settlements)
    {
        $this->router_factory = $routerFactory;
        /**
         * @var Settlement[]|Settlements $settlements
         */
        $this->settlements = $settlements;
        // $this->router(static::DEFAULT_ROUTER);
        foreach ($settlements as $settlement) {
            // $this->router($settlement->getRouter());
        }
        // $this->selectRouter(static::DEFAULT_ROUTER);
    }

    public function group(array $attributes, callable $routes)
    {
        $this->current_router->group($attributes, $routes);
    }

    public function addRoute($httpMethods, string $uri, $action): RouteInterface
    {
        return $this->current_router->addRoute($httpMethods, $uri, $action);
    }

    public function getRoute(string $name): ?RouteInterface
    {
        $delimiter = static::DELIMITER;
        $router = static::DEFAULT_ROUTER;
        $settlement = null;
        if (false !== strpos($name, $delimiter)) {
            $router = strstr($name, $delimiter, true);
            $name = substr(strstr($name, $delimiter), 2);
            $settlement = $this->settlements->getByRouter($router);
        }
        $route = $this->router($router)->getRoute($name);
        if ($route && $settlement) {
            return new SettlementRouteDecorator($route, $settlement);
        }
        return $route;
    }

    public function getRoutes(string $httpMethod = null): array
    {
        $all_routes = [];
        foreach ($this->settlements as $settlement) {
            $routes = $this->router($settlement->getRouter())->getRoutes($httpMethod);
            foreach ($routes as $method => $collection) {
                $collection = new Collection($collection);
                $settlement_collection = $collection->map(function (RouteInterface $item, $key) use ($settlement) {
                    return [new SettlementRouteDecorator($item, $settlement), $settlement->getPath() . $key];
                });
                if (!isset($all_routes[$method])) {
                    $all_routes[$method] = new Collection();
                }
                $all_routes[$method]->merge($settlement_collection);
            }
        }

        return $all_routes;
    }

    /**
     * @param string $settlement
     * @return array|RouteInterface[]
     */
    public function getBySettlement(string $settlement): array
    {
        $routes = [];
        if (strpos($settlement, 'default:') !== false) {
            foreach ($this->router('default')->getBySettlement(substr($settlement,8)) as $v) {
                $routes[$v->getName()] = $v;
            }
            return $routes;
        }
        if ($sett = $this->settlements->getByRouter($settlement)) {
            /**
             * @var RouteInterface $v
             */
            foreach ($this->router($settlement)->getRoutes('get') as $v) {
                $routes[$v->getName()] = new SettlementRouteDecorator($v, $sett);
            }

        }
        return $routes;
    }

    public function selectRouter(string $name = null): RouterInterface
    {
        $name = $this->castRouterName($name);
        if ($name === $this->current_router_name) {
            return $this;
        }
        if (!empty($this->previous_collections_names) && $name === $this->getLastPreviousRouterName()) {
            return $this->selectPreviousRouter();
        }
        if ($this->current_router_name !== null) {
            $this->previous_collections_names[] = $this->current_router_name;
        }

        $this->current_router = $this->router($name);
        $this->current_router_name = $name;

        return $this;
    }

    public function collection(string $name, callable $callback)
    {
        $current_router = $this->getCurrentRouterName();
        $this->selectRouter($name);
        if (is_callable($callback)) {
            $callback($this);
        }
        $this->selectRouter($current_router);
    }

    public function getCurrentRouterName(): string
    {
        return $this->current_router_name;
    }

    protected function getLastPreviousRouterName()
    {
        return !empty($this->previous_collections_names) ? end($this->previous_collections_names) : null;
    }

    public function selectPreviousRouter()
    {
        $name = $this->castRouterName(array_pop($this->previous_collections_names) ?? '');
        $this->selectRouter($name);
        array_pop($this->previous_collections_names);

        return $this;
    }

    protected function castRouterName(string $name = null)
    {
        if (in_array($name, ['', null])) {
            $name = static::DEFAULT_ROUTER;
        }

        return strtolower($name);
    }

    public function hasRouter($name)
    {
        return !is_null($this->settlements->getByRouter(\strtolower($name)));
    }

    public function getRouters()
    {
        return $this->routers;
    }

    /**
     * @param $name
     * @return Router
     */
    public function router($name = null): RouterInterface
    {
        $name = $this->castRouterName($name);

        if (!isset($this->routers[$name])) {
            $this->routers[$name] = $router = $this->router_factory->factoryRouter(['name' => $name]);
            if (method_exists($router, 'setName')) {
                $router->setName($name);
            }
        }
        return $this->routers[$name];
    }


    /**
     * @param $httpMethod
     * @param $uri
     * @return bool | RouteInterface
     */
    public function match(string $httpMethod, string $uri): ?RouteInterface
    {
        $uri = '/' . ltrim($uri, '\\/');
        $route = null;
        $settlement = $this->settlements->getByUrl($uri);
        if ($settlement) {
            $route = $this->router($settlement->getRouter())
                ->match($httpMethod, $settlement->getUriWithoutSettlement($uri));
        }
        if (!$route) {
            $route = $this->router(self::DEFAULT_ROUTER)->match($httpMethod, $uri);
        }

        return $route;
    }
}