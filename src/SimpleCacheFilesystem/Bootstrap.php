<?php

namespace Dissonance\SimpleCacheFilesystem;

use Dissonance\Core\AbstractBootstrap;
use Dissonance\Core\CoreInterface;
use Dissonance\Core\Events\CacheClear;
use Psr\SimpleCache\CacheInterface;

class Bootstrap extends AbstractBootstrap
{

    public function bootstrap(CoreInterface $app): void
    {
        $cache_path = $app('cache_path');
        if($cache_path) {

            $app->singleton(CacheInterface::class, function(CoreInterface $app) {
                return new SimpleCache($app['cache_path_core'], $app('config::cache_time',3600));
            },'cache');
            $app['listeners']->add(CacheClear::class, function($event) use ($app){
                $app[CacheInterface::class]->clear();
            });
            $app->alias(CacheInterface::class,\Dissonance\SimpleCacheFilesystem\SimpleCacheInterface::class);
        }
    }
}
