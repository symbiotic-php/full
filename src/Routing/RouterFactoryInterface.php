<?php

namespace Dissonance\Routing;


/**
 * Class Router
 * @package Dissonance\Routing
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
