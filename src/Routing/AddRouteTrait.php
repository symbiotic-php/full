<?php

namespace Dissonance\Routing;


/**
 * Trait HttpMethodsTrait
 * @package Dissonance\Routing
 * @method RouteInterface addRoute($httpMethods, string $uri, $action)
 *
 * @uses \Dissonance\Routing\RouterInterface::addRoute()
 */
trait AddRouteTrait
{


    /**
     * Add GET(HEAD) method route
     *
     * @param string $uri pattern
     * @param array|string|\Closure $action
     *
     * @return Route
     * @see addRoute()
     *
     */
    public function get(string $uri, $action): RouteInterface
    {
        return $this->addRoute(['GET', 'HEAD'], $uri, $action);
    }

    /**
     * Add HEAD method route
     *
     * @param string $uri pattern
     * @param array|string|\Closure $action
     *
     * @return Route
     *@see addRoute()
     *
     */
    public function head(string $uri, $action): RouteInterface
    {
        return $this->addRoute('HEAD', $uri, $action);
    }

    /**
     * Add POST method route
     *
     * @param string $uri pattern
     * @param array|string|\Closure $action
     *
     * @return Route
     *@see addRoute()
     *
     */
    public function post(string $uri, $action): RouteInterface
    {
        return $this->addRoute('POST', $uri, $action);
    }

    /**
     * Add PUT method route
     *
     * @param string $uri pattern
     * @param array|string|\Closure $action
     *
     * @return Route
     *@see addRoute()
     *
     */
    public function put(string $uri, $action): RouteInterface
    {
        return $this->addRoute('PUT', $uri, $action);
    }

    /**
     * Add DELETE method route
     *
     * @param string $uri pattern
     * @param array|string|\Closure $action
     *
     * @return Route
     *@see addRoute()
     *
     */
    public function delete(string $uri, $action): RouteInterface
    {
        return $this->addRoute('DELETE', $uri, $action);
    }

    /**
     * Add OPTIONS method route
     *
     * @param string $uri pattern
     * @param array|string|\Closure $action
     *
     * @return Route
     *@see addRoute()
     *
     */
    public function options(string $uri, $action): RouteInterface
    {
        return $this->addRoute('OPTIONS', $uri, $action);
    }

}
