<?php

declare(strict_types=1);

namespace Symbiotic\Routing;

use Symbiotic\Core\Support\Collection;


class SettlementsRouter extends Router implements RouterInterface
{
    const DELIMITER = '::';

    const DEFAULT_ROUTER = 'default';

    /**
     * @var RouterLazy[]|RouterInterface[]
     */
    protected array $routers = [];

    /**
     * @var RouterInterface|null
     */
    protected ?RouterInterface $current_router;

    /**
     * @var string|null
     */
    protected ?string $current_router_name;

    /**
     * @var array |string[]
     */
    protected array $previous_collections_names = [];


    public function __construct(
        protected RouterFactoryInterface $routerFactory,
        protected SettlementsInterface $settlements,
        protected AppsRoutesRepository $appsRoutesRepository
    ) {
        $this->router(static::DEFAULT_ROUTER);
    }

    /**
     * @param $name
     *
     * @return Router
     */
    public function router($name = null): RouterInterface
    {
        $name = $this->castRouterName($name);

        if (!isset($this->routers[$name])) {
            $this->routers[$name] = $router = $this->routerFactory->factoryRouter(['name' => $name]);
            if (method_exists($router, 'setName')) {
                $router->setName($name);
            }
        }
        return $this->routers[$name];
    }

    /**
     * @param string|null $name
     *
     * @return string
     */
    protected function castRouterName(string $name = null): string
    {
        if (empty($name)) {
            $name = static::DEFAULT_ROUTER;
        }

        return \strtolower($name);
    }

    /**
     * @param array    $attributes
     * @param \Closure $routes
     *
     * @return void
     */
    public function group(array $attributes, \Closure $routes): void
    {
        $this->current_router->group($attributes, $routes);
    }

    /**
     * @param                       $httpMethods
     * @param string                $uri
     * @param string|array|\Closure $action
     *
     * @return RouteInterface
     */
    public function addRoute($httpMethods, string $uri, string|array|\Closure $action): RouteInterface
    {
        return $this->current_router->addRoute($httpMethods, $uri, $action);
    }

    /**
     * @param string $name
     *
     * @return RouteInterface|null
     */
    public function getByName(string $name): ?RouteInterface
    {
        $data = static::splitRouterAndName($name);
        $settlement = $data[0] !== static::DEFAULT_ROUTER ? $this->settlements->getByRouter($data[0]) : null;

        $route = $this->router($data[0])->getByName($data[1]);
        if ($route && $settlement) {
            return new SettlementRouteDecorator($route, $settlement);
        }

        return $route;
    }

    /**
     * @param string|null $httpMethod
     *
     * @return array|RouteInterface[]
     */
    public function getRoutes(string $httpMethod = null): array
    {
        $all_routes = [];
        /**
         * @todo Why get all the routes of all settlements?
         */
        foreach ($this->settlements as $settlement) {
            $routes = $this->router($settlement->getRouter())->getRoutes($httpMethod);
            if (null !== $httpMethod) {
                $routes = [$httpMethod => $routes];
            }
            foreach ($routes as $method => $collection) {
                $collection = new Collection($collection);
                $settlement_collection = $collection->map(
                    function (RouteInterface $item, $key) use ($settlement) {
                        return [new SettlementRouteDecorator($item, $settlement), $settlement->getPath() . $key];
                    }
                );
                if (!isset($all_routes[$method])) {
                    $all_routes[$method] = new Collection();
                }
                $all_routes[$method]->merge($settlement_collection);
            }
        }

        return $all_routes;
    }

    /**
     * @return array|RouteInterface[]
     */
    public function getNamedRoutes(): array
    {
        $routes = [];
        foreach ($this->appsRoutesRepository->getProviders() as $v) {
            $routes = array_merge(
                $routes,
                $this->getSettlementRoutes($v->getAppId()),
                $this->getSettlementRoutes('api:' . $v->getAppId()),
                $this->getSettlementRoutes('backend:' . $v->getAppId())
            );
        }
        // Load all default routes
        /** @see SettlementsRoutingProvider::routesLoaderCallback()* */
        foreach ($this->router('default')->getNamedRoutes() as $k => $v) {
            $routes['default:' . $k] = $v;
        }

        return $routes;
    }

    /**
     * @param string                   $router_name
     * @param SettlementInterface|null $settlement
     *
     * @return array
     */
    public function getSettlementRoutes(string $router_name, SettlementInterface $settlement = null): array
    {
        $routes = [];
        if (null !== $settlement || $settlement = $this->settlements->getByRouter($router_name)) {
            foreach ($this->router($router_name)->getNamedRoutes() as $k => $v) {
                $routes[$router_name . '::' . $k] = new SettlementRouteDecorator($v, $settlement);
            }
        }

        return $routes;
    }

    /**
     * Returns the name of the router and the name of the route
     *
     * @param string $name router:sub::route_name
     *
     * @return array ['router:sub','route_name']
     */
    public static function splitRouterAndName(string $name): array
    {
        $delimiter = static::DELIMITER;
        $router = static::DEFAULT_ROUTER;
        // todo: replace to Str::sc($name)
        if (str_contains($name, $delimiter)) {
            $router = strstr($name, $delimiter, true);
            $name = substr(strstr($name, $delimiter), 2);
        }
        return [$router, $name];
    }

    /**
     * @param string $name
     *
     * @return array|RouteInterface[]
     * @deprecated
     */
    public function getByNamePrefix(string $name): array
    {
        $routes = [];
        if (str_starts_with($name, 'default:')) {
            foreach ($this->router('default')->getByNamePrefix(substr($name, 8)) as $v) {
                $routes['default:' . $v->getName()] = $v;
            }
            return $routes;
        }

        if ($sett = $this->settlements->getByRouter($name)) {
            /**
             * @var RouteInterface $v
             */
            foreach ($this->router($name)->getByNamePrefix(substr($name, 8)) as $v) {
                $routes[$v->getName()] = new SettlementRouteDecorator($v, $sett);
            }
        }
        return $routes;
    }

    /**
     * @param string   $name
     * @param callable $callback
     *
     * @return void
     */
    public function collection(string $name, callable $callback): void
    {
        $current_router = $this->getCurrentRouterName();
        $this->selectRouter($name);
        if (is_callable($callback)) {
            $callback($this);
        }
        $this->selectRouter($current_router);
    }

    /**
     * @return string
     */
    public function getCurrentRouterName(): string
    {
        return $this->current_router_name;
    }

    /**
     * @param string|null $name
     *
     * @return static
     */
    public function selectRouter(string $name = null): static
    {
        $name = $this->castRouterName($name);
        if ($name === $this->current_router_name) {
            return $this;
        }
        if (!empty($this->previous_collections_names) && $name === $this->getLastPreviousRouterName()) {
            return $this->selectPreviousRouter();
        }
        // todo: always string?
        if ($this->current_router_name !== null) {
            $this->previous_collections_names[] = $this->current_router_name;
        }

        $this->current_router = $this->router($name);
        $this->current_router_name = $name;

        return $this;
    }

    /**
     * @return string |null
     */
    protected function getLastPreviousRouterName(): ?string
    {
        return !empty($this->previous_collections_names) ? end($this->previous_collections_names) : null;
    }

    /**
     * @return $this
     */
    public function selectPreviousRouter(): static
    {
        $name = $this->castRouterName(array_pop($this->previous_collections_names) ?? '');
        $this->selectRouter($name);
        array_pop($this->previous_collections_names);

        return $this;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasRouter(string $name): bool
    {
        return !is_null($this->settlements->getByRouter(\strtolower($name)));
    }

    /**
     * @return RouterInterface[]|RouterLazy[]
     */
    public function getRouters(): array
    {
        return $this->routers;
    }

    /**
     * @param string $httpMethod
     * @param string $uri
     * @param bool   $checkOtherMethods
     *
     * @return RouteInterface|null
     * @todo  Add a route search for all request methods with the flag
     *
     */
    public function match(string $httpMethod, string $uri, bool $checkOtherMethods = false): ?RouteInterface
    {
        $uri = '/' . ltrim($uri, '\\/');
        $route = null;

        $settlement = $this->settlements->getByUrl($uri);

        if ($settlement) {
            $route = $this->router($settlement->getRouter())
                ->match($httpMethod, $settlement->getUriWithoutSettlement($uri));
            /// TODO: $route->setSettlement($settlement);
        }
        if (!$route) {
            $route = $this->router(self::DEFAULT_ROUTER)->match($httpMethod, $uri);
            /// TODO: $route->setSettlement($settlement);
        }

        return $route;
    }
}