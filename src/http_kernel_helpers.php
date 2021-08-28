<?php

namespace _DS;


use Symbiotic\Core\HttpKernelInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

if (!function_exists('_DS\\response')) {
    function response(int $code = 200, \Throwable $exception = null): ResponseInterface
    {
        return app(HttpKernelInterface::class)->response($code, $exception);
    }
}

if (!function_exists('_DS\\redirect')) {
    function redirect(string $uri, int $code = 301): ResponseInterface
    {
        $response = app(ResponseFactoryInterface::class)->createResponse($code);
        return $response->withHeader('Location', $uri);
    }
}
