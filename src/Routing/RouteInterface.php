<?php

namespace Symbiotic\Routing;

/**
 * Interface RouteInterface
 * @package Symbiotic\Routing
 */
interface RouteInterface
{
    public function getPath(): string;

    public function getName(): ?string;

    public function getAction(): array;

    public function isStatic():bool;
    /**
     * Массив посредников
     * @see Router::addRoute()
     * Можно добавлять люмба в массив
     * function(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface{}
     * @return array|string[]|\Closure[]
     */
    public function getMiddlewares():array;
    /**
     * Http scheme
     * true https
     * false http
     * @return bool
     */
    public function getSecure(): bool;

    /**
     * @return string|null domain name (www.example.com)
     */
    public function getDomain(): ?string;

    /**
     * @param string $domain
     * @return mixed
     */
    public function setDomain(string $domain);


    /**
     * @return callable|string|null
     */
    public function getHandler();

    public function setParam($key, $value);

    public function getParam($key);

    public function getParams(): array;
}
