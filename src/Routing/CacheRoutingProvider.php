<?php

namespace Symbiotic\Routing;

use Symbiotic\Core\ServiceProvider;


class CacheRoutingProvider extends ServiceProvider
{

    public function register(): void
    {
        $app = $this->app;
        $app->extend(RouterFactoryInterface::class, function($factory)use($app) {
           // return $factory;
            return new RouterCacheFactory($factory, $app('cache'));
        });
    }
}
