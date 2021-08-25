<?php

namespace Dissonance\Http;


use Dissonance\Core\{CoreInterface,BootstrapInterface};

use Psr\Http\Message\{
    StreamFactoryInterface,
    RequestFactoryInterface,
    ResponseFactoryInterface,
    ServerRequestFactoryInterface,
    UriFactoryInterface
};


class Bootstrap implements BootstrapInterface
{
    public function bootstrap(CoreInterface $app): void
    {
        $concrete = PsrHttpFactory::class;
        $app->singleton($concrete);
        $app->alias($concrete, 'http_factory');
        $app->alias($concrete, UriFactoryInterface::class);
        $app->alias($concrete, StreamFactoryInterface::class);
        $app->alias($concrete, ResponseFactoryInterface::class);
        $app->alias($concrete, ServerRequestFactoryInterface::class);
        $app->alias($concrete, RequestFactoryInterface::class);

    }
}