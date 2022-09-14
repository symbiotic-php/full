<?php

declare(strict_types=1);

namespace Symbiotic\Routing;


interface LazyRouterInterface extends NamedRouterInterface
{
    /**
     * @return bool
     */
    public function isLoadedRoutes(): bool;

    /**
     * @return void
     */
    public function loadRoutes(): void;
}
