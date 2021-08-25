<?php

namespace Dissonance\Routing;




/**
 * Interface RouterInterface
 * @package Dissonance\Routing
 */
interface RouterInterface
{

    public function setRoutesDomain(string $domain);
    /**
     * @param array |string $httpMethods
     * @param string $uri Uri pattern
     * @param array|string|\Closure  $action = [
     *
     *                    'uses' => '\\Module\\Http\\EntityController@edit',// string or \Closure
     *                     // optional params
     *                     'as' => 'module.entity.edit',
     *                     'module' => 'prefix_module',
     *                     'middleware' => ['\\Dissonance\\Http\\Middlewares\Auth', '\\Module\\Http\\Middlewares\Test']
     * ]
     *
     * @return RouteInterface
     */
    public function addRoute($httpMethods, string $uri, $action): RouteInterface;

    /**
     * Add GET(HEAD) method route
     *
     * @param string $uri pattern
     * @param array|string|\Closure $action
     *
     * @see addRoute()
     *
     * @return RouteInterface
     */
    public function get(string $uri, $action): RouteInterface;

    /**
     * Add POST method route
     *
     * @param string $uri pattern
     * @param array|string|\Closure $action
     *
     * @see addRoute()
     *
     * @return RouteInterface
     */
    public function post(string $uri, $action): RouteInterface;

    /**
     * Add HEAD method route
     *
     * @param string $uri pattern
     * @param array|string|\Closure $action
     *
     * @see addRoute()
     *
     * @return RouteInterface
     */
    public function head(string $uri, $action): RouteInterface;

    /**
     * Add PUT method route
     *
     * @param string $uri pattern
     * @param array|string|\Closure $action
     *
     * @see addRoute()
     *
     * @return RouteInterface
     */
    public function put(string $uri, $action): RouteInterface;

    /**
     * Add DELETE method route
     *
     * @param string $uri pattern
     * @param array|string|\Closure $action
     *
     * @see addRoute()
     *
     * @return RouteInterface
     */
    public function delete(string $uri, $action): RouteInterface;

    /**
     * Add OPTIONS method route
     *
     * @param string $uri pattern
     * @param array|string|\Closure $action
     *
     * @see addRoute()
     *
     * @return RouteInterface
     */
    public function options(string $uri, $action): RouteInterface;

    /**
     * Add group with params or subgroup
     *
     * @param array $attributes
     * @param callable $routes
     *
     * @return void
     */
    public function group(array $attributes, callable $routes);



    /**
     * @param $name
     *
     * @return RouteInterface|null
     */
    public function getRoute(string $name): ? RouteInterface;

    /**
     * @param string $settlement
     *
     * @return array|RouteInterface[]
     */
    public function getBySettlement(string $settlement):array;

    /**
     * @param null|string $httpMethod
     * @return array
     */
    public function getRoutes(string $httpMethod = null): array;

    /**
     * @param string $httpMethod
     * @param string $uri
     *
     * @return RouteInterface|null
     */
    public function match(string $httpMethod, string $uri): ? RouteInterface;


}
