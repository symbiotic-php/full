<?php

namespace Symbiotic\Routing;


/**
 * Class Router
 * @package Symbiotic\Routing
 *
 */
class RouterNamedFactory extends RouterFactory
{


    public function factoryRouter(array $params = []): RouterInterface
    {
        $factory = $this->app[RouterFactoryInterface::class];/// Получаем реальную фабрику через контейнер
        $router = new $this->router_class($factory);
        $router->setRoutesDomain($this->domain);
        if(isset($params['name']) && $router instanceof NamedRouterInterface) {
            $router->setName($params['name']);
        }
        return $router;
    }
}
