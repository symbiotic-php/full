<?php

namespace Dissonance\Http\Cookie;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;


class CookiesMiddleware implements MiddlewareInterface
{
    /**
     * @var CookiesInterface
     */
    protected $cookies;

    public function __construct(CookiesInterface $cookies)
    {
        $this->cookies = $cookies;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->cookies->setRequestCookies($request->getCookieParams());
        $response = $handler->handle($request);
        return $this->cookies->toResponse($response);
    }
}