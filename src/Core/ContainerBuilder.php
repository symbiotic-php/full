<?php

declare(strict_types=1);

namespace Symbiotic\Core;

use Psr\SimpleCache\CacheInterface;
use Symbiotic\Core\Bootstrap\LazyPackagesBootstrap;


class ContainerBuilder
{
    public function __construct(protected ?CacheInterface $cache = null)
    {
    }

    public function buildCore(array $config, string $key = null)
    {
        $key = $key ?: (string)\ftok(__FILE__, 'S');
        if (isset($config['bootstrappers'])) {
            array_unshift($config['bootstrappers'], LazyPackagesBootstrap::class);
        }
        $time = microtime();
        if ($this->cache) {
            $core = $this->cache->get($key, $time);
            if ($core === $time) {
                $core = new CachedCore($config);
                $core->setCache($this->cache, $key);
            }
        } else {
            $core = new Core($config);
        }

        return $core;
    }
}