<?php

declare(strict_types=1);

namespace Symbiotic\Routing;


/**
 * Class RouterLazy
 * @package Symbiotic\Routing
 * @property  RouterNamedFactory $router_factory
 */
class RouterLazy extends Router implements NamedRouterInterface, LazyRouterInterface
{
    use NamedRouterTrait;

    /**
     * @var bool
     */
    protected bool $loaded_routes = false;

    public function __construct(protected RouterFactoryInterface $routerFactory)
    {
        parent::__construct();
    }

    /**
     * @param string $name
     *
     * @return RouteInterface|null
     */
    public function getByName(string $name): ?RouteInterface
    {
        $this->loadRoutes();
        return parent::getByName($name);
    }

    /**
     * @return void
     */
    public function loadRoutes(): void
    {
        if (!$this->loaded_routes) {
            $this->loaded_routes = true;
            $this->routerFactory->loadRoutes($this);
        }
    }

    /**
     * @return array
     */
    public function getNamedRoutes(): array
    {
        $this->loadRoutes();
        return parent::getNamedRoutes();
    }

    /**
     * @param string $name
     *
     * @return array|RouteInterface[]
     */
    public function getByNamePrefix(string $name): array
    {
        $this->loadRoutes();
        return parent::getByNamePrefix($name);
    }

    /**
     * @param string|null $httpMethod
     *
     * @return array
     */
    public function getRoutes(string $httpMethod = null): array
    {
        $this->loadRoutes();
        return parent::getRoutes($httpMethod);
    }

    /**
     * @return bool
     */
    public function isLoadedRoutes(): bool
    {
        return $this->loaded_routes;
    }

    /**
     * @return string[]
     */
    public function __sleep()
    {
        return [
            'named_routes',
            'routes',
            'loaded_routes',
            'name',
            'params'
        ];
    }

    /**
     * @return void
     */
    public function __wakeup()
    {
        $this->loaded_routes = true;
    }


}
