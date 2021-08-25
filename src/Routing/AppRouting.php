<?php

namespace Dissonance\Routing;


class AppRouting implements AppRoutingInterface
{
    /**
     * @see \Dissonance\Packages\PackagesRepository::addPackage()
     */
    protected string $app_id;

    /**
     * @var string|null
     */
    protected ?string $controllers_namespace = null;


    public function __construct(string $app_id, string $controllers_namespace = null)
    {
        $this->app_id = $app_id;
        $this->controllers_namespace = $controllers_namespace;
    }


    public function backendRoutes(RouterInterface $router)
    {

    }

    public function frontendRoutes(RouterInterface $router)
    {

    }

    public function apiRoutes(RouterInterface $router)
    {

    }

    public function defaultRoutes(RouterInterface $router)
    {

    }


    public function loadBackendRoutes(RouterInterface $router)
    {
        $options = $this->getRoutingOptions();
        unset($options['prefix']);
        unset($options['as']);
        $router->group(
            $options,
            $this->getLoadRoutesCallback('backendRoutes')
        );
    }


    public function loadApiRoutes(RouterInterface $router)
    {
        $options = $this->getRoutingOptions();
        unset($options['prefix']);
        unset($options['as']);
        $router->group(
            $options,
            $this->getLoadRoutesCallback('apiRoutes')
        );

    }

    public function loadFrontendRoutes(RouterInterface $router)
    {
        $options = $this->getRoutingOptions();
        unset($options['prefix']);
        unset($options['as']);
        $router->group(
            $options,
            $this->getLoadRoutesCallback('frontendRoutes')
        );

    }

    public function loadDefaultRoutes(RouterInterface $router)
    {
        $router->group(
            ['namespace' => $this->controllers_namespace],
            $this->getLoadRoutesCallback('defaultRoutes')
        );
    }

    protected function getRoutingOptions()
    {
        $id = $this->app_id;
        return [
            'prefix' => $id,
            'app' => $id,
            'as' => $id,
            'namespace' => $this->controllers_namespace,
        ];
    }

    protected function loadPrefixRoutes(RouterInterface $router, $function)
    {
        $router->group(
            $this->getRoutingOptions(),
            $this->getLoadRoutesCallback($function)
        );
    }

    protected function getLoadRoutesCallback($method)
    {
        // TODO: переделать в Switch!
        return function (RouterInterface $router) use ($method) {
            $this->{$method}($router);
        };
    }


    /**
     * @return string
     */
    public function getAppId(): string
    {
        return $this->app_id;
    }
}