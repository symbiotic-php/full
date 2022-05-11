<?php

namespace Symbiotic\Routing;


use Symbiotic\Container\DIContainerInterface;

/**
 * Class RouterFactory
 * @package Symbiotic\Routing
 */
class RouterFactory implements RouterFactoryInterface
{

    protected ?string $router_class = null;

    /**
     * @var callable|null
     */
    protected $routes_loader_callback = null;

    /**
     * @var string|null
     */
    protected ?string $domain = null;

    /**
     * @var DIContainerInterface
     */
    protected DIContainerInterface $app;

    public function __construct(DIContainerInterface $app, string $router_class, callable $routes_loader_callback, string $domain = null)
    {
        $this->app = $app;
        $this->router_class = $router_class;
        $this->domain = $domain;
        $this->routes_loader_callback = $routes_loader_callback;

    }

    /**
     * @param array $params
     * @return RouterInterface
     */
    public function factoryRouter(array $params = []): RouterInterface
    {
        $router = new $this->router_class();
        $router->setRoutesDomain($this->domain);

        return $router;
    }

    public function loadRoutes(RouterInterface $router)
    {
        $callable = $this->routes_loader_callback;
        $callable($router);
    }

}
