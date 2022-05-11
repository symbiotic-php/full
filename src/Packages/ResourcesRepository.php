<?php

namespace Symbiotic\Packages;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Class AssetsRepository
 * @package Symbiotic\Apps
 *
 */
class ResourcesRepository implements ResourcesRepositoryInterface, AssetsRepositoryInterface, TemplatesRepositoryInterface
{

    protected array $packages = [];

    /**
     * @var TemplateCompiler
     */
    protected TemplateCompiler $compiler;

    /**
     * @var StreamFactoryInterface
     */
    protected StreamFactoryInterface $factory;

    /**
     * @var PackagesRepositoryInterface
     */
    protected PackagesRepositoryInterface $packages_repository;

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
     * @throws \Exception|ResourceException
     */
    public function getAssetFileStream(string $package_id, string $path): StreamInterface
    {
        return $this->getPathTypeFileStream($package_id, $path, 'public_path');
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
        $repository = $this->packages_repository;
        if ($repository->has($package_id)) {
            $assets = [];
            $package_config = $repository->get($package_id);
            foreach (['public_path' => 'assets', 'resources_path' => 'resources'] as $k => $v) {
                if (!empty($package_config[$k]) || isset($package_config['app'])) {
                    $assets[$k] = rtrim($package_config['base_path'], '\\/')
                        . \_S\DS
                        . (isset($package_config[$k]) ? trim($package_config[$k], '\\/') : $v);

                }
            }
            if (isset($assets[$path_type])) {
                $full_path = $assets[$path_type] . '/' . ltrim($path, '/\\');
                if (!\is_readable($full_path) || !($res = \fopen($full_path, 'r'))) {
                    throw new ResourceException('File is not exists or not readable!', $full_path);
                }
                return $this->factory->createStreamFromResource($res);
            }
            throw new \Exception(ucfirst($path_type) . ' is not defined!');
        }
        throw new \Exception('Package not found [' . $package_id . ']!');
    }

    protected function cleanPath(string $path)
    {
        return preg_replace('!\.\.[/\\\]!', '', $path);
    }

    /**
     * @param string $package_id
     * @param string $path layouts/base/index or /layouts/base/index  - real path(module_root/resources/views/layouts/base/index)
     * if use config resources  storage as strings
     * layouts/base/index or /layouts/base/index  - $config['resources']['views']['layouts/base/index']
     *
     * @return string
     *
     * @throws \Exception|ResourceException
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

    /**
     * @param string $package_id
     * @param string $path
     * @return StreamInterface
     * @throws \Exception|ResourceException
     */
    public function getResourceFileStream(string $package_id, string $path): StreamInterface
    {
        return $this->getPathTypeFileStream($package_id, $path, 'resources_path');
    }


}