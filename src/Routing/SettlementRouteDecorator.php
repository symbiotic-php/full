<?php

namespace Dissonance\Routing;


class SettlementRouteDecorator implements RouteInterface
{
    /**
     * @var RouteInterface
     */
    protected $route = null;

    /**
     * @var Settlement|null
     */
    protected $settlement = null;

    /**
     * @var string
     */
    protected $path;

    public function __construct(RouteInterface $route, Settlement $settlement)
    {
        $this->route = $route;
        $this->settlement = $settlement;
        $this->path =  $this->settlement->getPath().'/'.ltrim($this->route->getPath(),'\\/');
    }
    public function getName() : string
    {
        return $this->route->getName();
    }

    public function isStatic():bool
    {
        return $this->route->isStatic();
    }

    public function getAction() : array
    {
        return $this->route->getAction();
    }
    public function getMiddlewares() : array
    {
        return $this->route->getMiddlewares();
    }

    public function getPath(): string
    {
        return  $this->path;
    }

    public function getHandler()
    {
        return $this->route->getHandler();
    }

    public function setParam($key, $value)
    {
        return $this->route->setParam($key, $value);
    }

    public function getParam($key)
    {
        return $this->route->getParam($key);
    }
    public function getParams(): array
    {
        return $this->route->getParams();
    }

    public function getSecure(): bool
    {
        return $this->route->getSecure();
    }
    public function getDomain(): ? string
    {
        return $this->route->getDomain();
    }

    public function setDomain(string $domain)
    {
        return $this->route->setDomain($domain);
    }


}