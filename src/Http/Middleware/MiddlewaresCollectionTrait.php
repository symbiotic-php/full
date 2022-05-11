<?php

namespace Symbiotic\Http\Middleware;

use Psr\Http\Server\MiddlewareInterface;


trait MiddlewaresCollectionTrait
{
    /**
     * @var MiddlewareInterface[]
     */
    protected array $middleware = [];

    /**
     * Add a middleware to the end of the stack.
     *
     * @param MiddlewareInterface $middleware
     *
     * @return void
     */
    public function append(MiddlewareInterface $middleware)
    {
        array_push($this->middleware, $middleware);
    }

    /**
     * Add a middleware to the beginning of the stack.
     *
     * @param MiddlewareInterface $middleware
     *
     * @return void
     */
    public function prepend(MiddlewareInterface $middleware)
    {
        array_unshift($this->middleware, $middleware);
    }

}