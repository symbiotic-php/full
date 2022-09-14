<?php

declare(strict_types=1);

namespace Symbiotic\Routing;


class CacheLazyRouterDecorator extends CacheRouterDecorator implements NamedRouterInterface, LazyRouterInterface
{
    /**
     * @var bool
     */
    protected bool $loaded = false;

    /**
     * @return bool
     */
    public function isLoadedRoutes(): bool
    {
        return $this->loaded;
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
     * @return array
     */
    public function getNamedRoutes(): array
    {
        $this->loadRoutes();
        return parent::getNamedRoutes();
    }

    /**
     * @return void
     */
    public function loadRoutes(): void
    {
        if (!$this->loaded) {
            $this->factory->loadRoutes($this);
            $this->loaded = true;
        }
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
     * @param string $name
     *
     * @return array
     */
    public function getByNamePrefix(string $name): array
    {
        $this->loadRoutes();
        return parent::getByNamePrefix($name);
    }

    /**
     * @param string $httpMethod
     * @param string $uri
     *
     * @return RouteInterface|null
     */
    public function match(string $httpMethod, string $uri): ?RouteInterface
    {
        $this->loadRoutes();
        return parent::match($httpMethod, $uri);
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function setName(string $name): void
    {
        $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->call(__FUNCTION__, func_get_args());
    }
}
