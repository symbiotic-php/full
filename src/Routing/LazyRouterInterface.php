<?php

namespace Dissonance\Routing;


/**
 * Interface NamedRouterInterface
 * @package Dissonance\Routing
 */
interface LazyRouterInterface extends NamedRouterInterface
{

    public function isLoadedRoutes():bool;

    /**
     * @return mixed
     */
    public function loadRoutes();
}
