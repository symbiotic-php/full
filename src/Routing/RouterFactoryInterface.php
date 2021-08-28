<?php

namespace Symbiotic\Routing;


/**
 * Class Router
 * @package Symbiotic\Routing
 *
 */
interface RouterFactoryInterface
{

    /**
     * @param array $params
     * @return RouterInterface
     */
    public function factoryRouter(array $params = []) :  RouterInterface;

    public function loadRoutes(RouterInterface $router);
}
