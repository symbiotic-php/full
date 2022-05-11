<?php

namespace Symbiotic\Routing;


interface AppRoutingInterface
{
    /**
     * @param RouterInterface $router
     * @return mixed
     */
    public function loadBackendRoutes(RouterInterface $router);

    /**
     * @param RouterInterface $router
     * @return mixed
     */
    public function loadApiRoutes(RouterInterface $router);

    public function loadFrontendRoutes(RouterInterface $router);

    public function loadDefaultRoutes(RouterInterface $router);

    /**
     * @return string
     */
    public function getAppId(): string;
}