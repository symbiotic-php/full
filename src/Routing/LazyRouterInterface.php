<?php

namespace Symbiotic\Routing;


/**
 * Interface NamedRouterInterface
 * @package Symbiotic\Routing
 */
interface LazyRouterInterface extends NamedRouterInterface
{

    public function isLoadedRoutes():bool;

    /**
     * @return mixed
     */
    public function loadRoutes();
}
