<?php

namespace Symbiotic\Packages;


use Symbiotic\Core\Support\Arr;

class PackagesLoaderFilesystem implements PackagesLoaderInterface
{
    /**
     * @var array
     */
    protected array $scan_dirs = [];
    /**
     * @var int
     */
    protected int $max_depth = 3;


    /**
     * PackagesLoader constructor.
     * @param array $scan_dirs
     * @param int $max_depth
     */
    public function __construct(array $scan_dirs = [], int $max_depth = 3)
    {
        $this->scan_dirs = $scan_dirs;
        $this->max_depth = $max_depth;
    }

    public function load(PackagesRepositoryInterface $repository)
    {
        $packages = [];
        if (!empty($this->scan_dirs)) {
            foreach ($this->scan_dirs as $dir) {
                if (is_dir($dir) && is_readable($dir)) {
                    $packages = array_merge($packages, $this->getDirPackages($dir));
                } else {
                    throw new \Exception('Directory [' . $dir . '] is not readable or not exists!');
                }
            }
            foreach ($packages as $v) {
                $repository->addPackage($v);
            }
        }
    }

    protected function getDirPackages($dir)
    {
        $files = $packages = [];

        for ($i = 0; $i < $this->max_depth; $i++) {
            $level = str_repeat('/*', $i);
            $files = array_merge($files, glob($dir . $level . '/composer.json', GLOB_NOSORT));
        }

        foreach ($files as $file) {
            if (\is_readable($file)) {
                $config = Arr::get(@\json_decode(file_get_contents($file), true), 'extra.symbiotic');
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
