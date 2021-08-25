<?php

namespace Dissonance\Packages;


use Dissonance\Core\Support\Arr;
use Psr\SimpleCache\CacheInterface;

class PackagesLoaderFilesystem implements PackagesLoaderInterface
{
    /**
     * @var array
     */
    protected $scan_dirs = [];
    /**
     * @var int
     */
    protected $max_depth = 3;

    /**
     * @var null |CacheInterface
     */
    protected $cache = null;

    /**
     * PackagesLoader constructor.
     * @param array $scan_dirs
     * @param null|CacheInterface $cache
     * @param int $max_depth
     */
    public function __construct(array $scan_dirs = [], int $max_depth = 3)
    {
        $this->scan_dirs = $scan_dirs;
        $this->max_depth = $max_depth;
    }

    public function load(PackagesRepositoryInterface $repository)
    {
        /**
         * @var null|CacheInterface $cache
         */
        $cache = $this->cache;
        $key = 'packages_filesystem';
        if ($cache && ($packages = $cache->get($key)) && is_array($packages)) {

        } else {
            $packages = [];
            if (!empty($this->scan_dirs)) {
                foreach ($this->scan_dirs as $dir) {
                    if (is_dir($dir) && is_readable($dir)) {
                        $packages = array_merge($packages, $this->getDirPackages($dir));
                    } else {
                        throw new \Exception('Directory [' . $dir . '] is not readable or not exists!');
                    }
                }
            }

            if ($cache) {
                $cache->set($key, $packages);
            }
        }

        foreach ($packages as $v) {
            $repository->addPackage($v);
        }

    }

    protected function getDirPackages($dir)
    {
        $packages = [];

        $files = array_merge(
            glob($dir . '/*/composer.json', GLOB_NOSORT),
            glob($dir . '/*/*/composer.json', GLOB_NOSORT)
        );

        foreach ($files as $file) {
            if (\is_readable($file)) {
                $config = Arr::get(@\json_decode(file_get_contents($file), true), 'extra.dissonance');
                if (is_array($config)) {
                    $app = Arr::get($config, 'app');
                    $config['base_path'] = dirname($file);
                    if (is_array($app)) {
                        $app['base_path'] = $config['base_path'];
                        $config['app'] = $app;
                    }
                    $packages[] = $config;
                }
            }
        }
        return $packages;
    }


}
