<?php

declare(strict_types=1);

namespace Symbiotic\Routing;


class SettlementRouteDecorator implements RouteInterface
{
    /**
     * @var string
     */
    protected string $path;

    /**
     * @param RouteInterface      $route
     * @param SettlementInterface $settlement
     */
    public function __construct(protected RouteInterface $route, protected SettlementInterface $settlement)
    {
        $this->path = $this->settlement->getPath() . '/' . ltrim($this->route->getPath(), '\\/');
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->route->getName();
    }

    /**
     * @return bool
     */
    public function isStatic(): bool
    {
        return $this->route->isStatic();
    }

    /**
     * @return array
     */
    public function getAction(): array
    {
        return $this->route->getAction();
    }

    /**
     * @return array|\Closure[]|string[]
     */
    public function getMiddlewares(): array
    {
        return $this->route->getMiddlewares();
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return mixed
     */
    public function getHandler(): mixed
    {
        return $this->route->getHandler();
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function setParam(string $key, mixed $value): void
    {
        $this->route->setParam($key, $value);
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getParam(string $key): mixed
    {
        return $this->route->getParam($key);
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->route->getParams();
    }

    /**
     * @return bool
     */
    public function getSecure(): bool
    {
        return $this->route->getSecure();
    }

    /**
     * @return string|null
     */
    public function getDomain(): ?string
    {
        return $this->route->getDomain();
    }

    /**
     * @param string $domain
     *
     * @return $this
     */
    public function setDomain(string $domain): static
    {
        $this->route->setDomain($domain);
        return $this;
    }
}