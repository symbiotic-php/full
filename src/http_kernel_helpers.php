<?php

namespace _DS;


use Dissonance\Core\HttpKernelInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

function response(int $code = 200,\Throwable $exception = null):ResponseInterface
{
   return app(HttpKernelInterface::class)->response($code, $exception);
}

function redirect(string $uri, int $code = 301):ResponseInterface
{
    $response = app(ResponseFactoryInterface::class)->createResponse($code);
    return $response->withHeader('Location',  $uri);
}