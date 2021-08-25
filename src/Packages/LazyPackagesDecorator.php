<?php

namespace Dissonance\Packages;

use Dissonance\Filesystem\ArrayStorageTrait;
use Dissonance\Storage\RememberingInterface;

class LazyPackagesDecorator implements PackagesRepositoryInterface, RememberingInterface
{
    use ArrayStorageTrait;

    /**
     * @var PackagesRepositoryInterface
     */
    protected $repository;

    protected ?array $meta = null;

    protected ?array $packages = null;

    public function __construct(PackagesRepositoryInterface $repository, string $storage_path = null)
    {
        $this->repository = $repository;
        if ($storage_path) {
            $this->setStoragePath($storage_path);
        }
    }

    public function getIds(): array
    {
        return $this->getMeta()['ids'];
    }

    public function has($id): bool
    {
        return isset($this->getIds()[$id]);
    }

    public function get(string $id): array
    {
        $packages = $this->getPackages();
        if (!isset($this->getIds()[$id])) {
            throw new \Exception("Package [$id] not found!");
        }
        return $packages[$id];
    }

    public function getPackages(): array
    {
        if (null === $this->packages) {
            $this->packages = $this->remember('packages_data.php', function () {
                $this->repository->load();
                return $this->repository->getPackages();
            });
        }

        return $this->packages;
    }

    public function addPackagesLoader(PackagesLoaderInterface $loader): void
    {
        $this->repository->addPackagesLoader($loader);
    }

    public function addPackage(array $config): void
    {
        $this->repository->addPackage($config);
    }

    public function getBootstraps(): array
    {
        return $this->getMeta()['bootstraps'];
    }

    public function load(): void
    {
        /**
         * @see LazyPackagesDecorator::getMeta()
         * @see LazyPackagesDecorator::getPackages()
         */
    }

    /**
     * @return array = ['ids'=>[],'bootstraps' =>[]];
     */
    protected function getMeta(): array
    {
        if (null===$this->meta) {
            $this->meta = $this->remember('packages_meta.php', function () {
                $this->repository->load();
                return [
                    'bootstraps' => $this->repository->getBootstraps(),
                    'ids' => $this->repository->getIds(),
                ];
            });
        }

        return $this->meta;
    }

}