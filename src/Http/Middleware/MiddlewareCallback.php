<?php

namespace Symbiotic\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;


/**
 * Class MiddlewareHandler
 * @package Symbiotic\Http\Middleware
 * @category Symbiotic\Http
 *
 * @author shadowhand https://github.com/shadowhand
 * @link https://github.com/jbboehr/dispatch - base source
 */
class MiddlewareCallback implements MiddlewareInterface
{
    /**
     * @var \Closure function()
     */
    protected \Closure $middleware;


    /**
     * MiddlewareCallback constructor.
     * @param \Closure $middleware
     */
    public function __construct(\Closure $middleware)
    {
        $this->middleware = $middleware;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $closure = $this->middleware;
        return $closure($request, $handler);
    }
}