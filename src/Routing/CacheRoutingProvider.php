<?php

declare(strict_types=1);

namespace Symbiotic\Routing;

use Psr\SimpleCache\CacheInterface;
use Symbiotic\Core\ServiceProvider;


class CacheRoutingProvider extends ServiceProvider
{

    public function register(): void
    {
        $app = $this->app;
        $app->extend(RouterFactoryInterface::class, function ($factory) use ($app) {
            return new RouterCacheFactory($factory, $app(CacheInterface::class));
        });
    }
}
