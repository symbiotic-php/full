<?php

namespace Symbiotic\Routing;


/**
 * Class RouterLazy
 * @package Symbiotic\Routing
 * @property  RouterNamedFactory $router_factory
 */
class RouterLazy extends Router implements NamedRouterInterface,LazyRouterInterface
{
    use NamedRouterTrait;

    /**
     * @var bool
     */
    protected bool $loaded_routes = false;

    protected RouterFactoryInterface|null $router_factory = null;


    public function __construct(RouterFactoryInterface $routerFactory)
    {
        $this->router_factory = $routerFactory;
        parent::__construct();
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function getRoute(string $name) : ? RouteInterface
    {
        $this->loadRoutes();
        return parent::getRoute($name);
    }

    public function getBySettlement(string $settlement):array
    {
        $this->loadRoutes();
        return parent::getBySettlement($settlement);
    }



    /**
     * @param string|null $httpMethod
     * @return array
     */
    public function getRoutes(string $httpMethod = null): array
    {
        $this->loadRoutes();
        return parent::getRoutes($httpMethod);
    }


    public function isLoadedRoutes():bool
    {
        return $this->loaded_routes;
    }

    /**
     *
     */
    public function loadRoutes()
    {
        if(!$this->loaded_routes)  {
            $this->loaded_routes = true;
            $this->router_factory->loadRoutes($this);
        }
    }

    public function __sleep()
    {
        return [
            'named_routes',
            'routes',
            'loaded_routes',
            'name',
            'domain'

        ];
    }

    public function __wakeup()
    {
        $this->loaded_routes = true;
    }


}
