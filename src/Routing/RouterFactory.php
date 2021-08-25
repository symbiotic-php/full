<?php

namespace Dissonance\Routing;


use Dissonance\Container\DIContainerInterface;

/**
 * Class RouterFactory
 * @package Dissonance\Routing
 */
class RouterFactory implements RouterFactoryInterface
{

    protected $router_class = null;

    protected $routes_loader_callback = null;

    /**
     * @var string|null
     */
    protected $domain = null;

    /**
     * @var DIContainerInterface
     */
    protected $app;

    public function __construct(DIContainerInterface $app, string $router_class, callable $routes_loader_callback, string $domain = null)
    {
        $this->app = $app;
        $this->router_class = $router_class;
        $this->domain = $domain;
        $this->routes_loader_callback = $routes_loader_callback;

    }

    /**
     * @param array|null $params
     * @return RouterInterface
     */
    public function factoryRouter(array $params = []) :  RouterInterface
    {
        $router = new $this->router_class();
        $router->setRoutesDomain($this->domain);

        return $router;
    }

    public function loadRoutes(RouterInterface $router)
    {
        $callable =  $this->routes_loader_callback;
        $callable($router);
    }

}
