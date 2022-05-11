<?php

namespace _S;


use Symbiotic\Core\HttpKernelInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

if (!function_exists('_S\\response')) {
    function response(int $code = 200, \Throwable $exception = null): ResponseInterface
    {
        return core(HttpKernelInterface::class)->response($code, $exception);
    }
}

if (!function_exists('_S\\redirect')) {
    function redirect(string $uri, int $code = 301): ResponseInterface
    {
        $response = core(ResponseFactoryInterface::class)->createResponse($code);
        return $response->withHeader('Location', $uri);
    }
}
