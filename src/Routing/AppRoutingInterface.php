<?php

namespace Dissonance\Routing;


interface AppRoutingInterface
{
    public function loadBackendRoutes(RouterInterface $router);

    public function loadApiRoutes(RouterInterface $router);

    public function loadFrontendRoutes(RouterInterface $router);

    public function loadDefaultRoutes(RouterInterface $router);

    /**
     * @return string
     */
    public function getAppId(): string;
}