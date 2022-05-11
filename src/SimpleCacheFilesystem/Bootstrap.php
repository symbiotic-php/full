<?php

namespace Symbiotic\SimpleCacheFilesystem;

use Psr\SimpleCache\CacheInterface;
use Symbiotic\Core\AbstractBootstrap;
use Symbiotic\Core\CoreInterface;
use Symbiotic\Core\Events\CacheClear;

class Bootstrap extends AbstractBootstrap
{

    public function bootstrap(CoreInterface $core): void
    {
        $cache_path = $core('cache_path');
        if ($cache_path) {

            $core->singleton(CacheInterface::class, function (CoreInterface $app) {
                return new SimpleCache($app['cache_path_core'], $app('config::cache_time', 3600));
            }, 'cache');
            $core['listeners']->add(CacheClear::class, function (CacheClear $event) use ($core) {
                if ($event->getPath() === 'all') {
                    $core[CacheInterface::class]->clear();
                }
            });
            $core->alias(CacheInterface::class, \Symbiotic\SimpleCacheFilesystem\SimpleCacheInterface::class);
        }
    }
}
