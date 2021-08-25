<?php


namespace Dissonance\Core;

use Dissonance\Core\Bootstrap\LazyPackagesBootstrap;
use Psr\SimpleCache\CacheInterface;

class ContainerBuilder
{
    /**
     * @var CacheInterface |null
     */
    protected  $cache;

    public function __construct(CacheInterface $cache = null)
    {
        $this->cache = $cache;
    }

    public function buildCore(array $config, string $key = null)
    {
        $key = $key ?: \md5(__FILE__ . CachedCore::class);
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
            // если репозиторий не будет кешироваться, то сработает наш декоратор
        }

        return $core;

    }
}