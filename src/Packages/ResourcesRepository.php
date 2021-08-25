<?php

namespace Dissonance\Packages;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Class AssetsRepository
 * @package Dissonance\Apps
 *
 */
class ResourcesRepository implements ResourcesRepositoryInterface, AssetsRepositoryInterface, TemplatesRepositoryInterface
{

    protected $packages = [];

    /**
     * @var TemplateCompiler
     */
    protected $compiler;

    /**
     * @var StreamFactoryInterface
     */
    protected $factory;

    /**
     * @var PackagesRepositoryInterface
     */
    protected $packages_repository;

    /**
     * ResourcesRepository constructor.
     *
     * @param TemplateCompiler $compiler
     * @param StreamFactoryInterface $factory
     * @param PackagesRepositoryInterface $packages
     */
    public function __construct(TemplateCompiler $compiler, StreamFactoryInterface $factory, PackagesRepositoryInterface $packages)
    {
        $this->compiler = $compiler;
        $this->factory = $factory;
        $this->packages_repository = $packages;
    }


    /**
     * @param string $package_id
     * @param string $path
     * @return StreamInterface
     * @throws \Exception
     */
    public function getAssetFileStream(string $package_id, string $path): StreamInterface
    {
        return $this->getPathTypeFileStream($package_id, $path, 'public_path');
    }

    /**
     * @param string $package_id
     * @param string $path
     * @return StreamInterface
     * @throws \Exception
     */
    public function getResourceFileStream(string $package_id, string $path): StreamInterface
    {
        return $this->getPathTypeFileStream($package_id, $path, 'resources_path');
    }

    /**
     * @param string $package_id
     * @param string $path layouts/base/index or /layouts/base/index  - real path(module_root/resources/views/layouts/base/index)
     * if use config resources  storage as strings
     * layouts/base/index or /layouts/base/index  - $config['resources']['views']['layouts/base/index']
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getTemplate(string $package_id, string $path): string
    {
        $base_name = basename($path);
        if (strpos($base_name, '.') === false) {
            $path .= '.blade.php';
        }
        $file = $this->getResourceFileStream($package_id, 'views/' . ltrim($this->cleanPath($path), '\\/'));

        return $this->compiler->compile($path, $file->getContents());

    }

    protected function cleanPath(string $path)
    {
        return preg_replace('!\.\.[/\\\]!', '', $path);
    }

    /**
     * @param string $package_id
     * @param string $path
     * @param string $path_type resources array key 'public_path' or 'resources_path'
     * @return StreamInterface|null
     */
    protected function getPathTypeFileStream(string $package_id, string $path, string $path_type): ?StreamInterface
    {
        $path = $this->cleanPath($path);

        if ($this->packages_repository->has($package_id)) {
            $assets = [];
            $package_config = $this->packages_repository->get($package_id);
            foreach (['public_path' => 'assets', 'resources_path' => 'resources'] as $k => $v) {
                if (!empty($package_config[$k]) || isset($package_config['app'])) {
                    $assets[$k] = rtrim($package_config['base_path'], '\\/')
                        . \_DS\DS
                        . (isset($package_config[$k]) ? trim($package_config[$k], '\\/') : $v);

                }
            }
            if (isset($assets[$path_type])) {
                $full_path = $assets[$path_type] . '/' . ltrim($path, '/\\');
                if (!\is_readable($full_path)) {
                    throw new \Exception('File is not exists or not readable [' . $full_path . ']!');
                }
                return $this->factory->createStreamFromResource(\fopen($full_path, 'r'));
            }
        }
        throw new \Exception('Package not found [' . $package_id . ']!');
    }


}