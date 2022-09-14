<?php

declare(strict_types=1);

namespace Symbiotic\Routing;


class RouterNamedFactory extends RouterFactory
{
    public function factoryRouter(array $params = []): RouterInterface
    {
        /**
         * We get a real factory through a container
         */
        $factory = $this->app[RouterFactoryInterface::class];
        /**
         * @var RouterInterface $router
         */
        $router = new $this->router_class($factory);
        $router->setParams(array_merge($this->params, $params));
        if (isset($params['name']) && $router instanceof NamedRouterInterface) {
            $router->setName($params['name']);
        }

        return $router;
    }
}
