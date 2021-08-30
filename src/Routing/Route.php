<?php

namespace Symbiotic\Routing;


class Route implements RouteInterface
{


    protected $action = [];

    protected $pattern = '';

    protected $request_params = [];

    /**
     * Route constructor.
     * @param string $uri
     * @param array $action
     */
    public function __construct(string $uri, array $action)
    {
        $this->pattern = trim($uri, '/');
        $this->action = $action;
    }

    public function getName(): string
    {
        return $this->action['as'] ?? $this->pattern;
    }

    public function isStatic():bool
    {
        return strpos($this->getPath(),'{')===false;
    }

    public function getAction(): array
    {
        return $this->action;
    }
    public function getMiddlewares(): array
    {
        return isset($this->action['middleware'])?$this->action['middleware']:[];
    }

    public function setDomain(string $domain)
    {
        $this->action['domain'] = $domain;

        return $this;
    }

    public function getSecure(): bool
    {
        return isset($this->action['secure']) ? (bool)$this->action['secure'] : false;
    }

    public function getDomain(): ?string
    {
        return $this->action['domain'] ?? null;
    }


    public function getApp(): ?string
    {
        return $this->action['app'] ?? null;
    }

    public function getPath(): string
    {
        return $this->pattern;
    }

    public function getHandler()
    {
        return $this->action['uses'];
    }

    /**
     * @param $key
     * @param $value
     */
    public function setParam($key, $value)
    {
        $this->request_params[$key] = $value;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->request_params;
    }

    /**
     * @param $name
     *
     * @return string|null
     */
    public function getParam($name)
    {
        return $this->request_params[$name] ?? null;
    }
}