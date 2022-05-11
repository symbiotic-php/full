<?php

namespace Symbiotic\Packages;

use Symbiotic\Filesystem\ArrayStorageTrait;
use Symbiotic\Storage\RememberingInterface;

class LazyPackagesDecorator implements PackagesRepositoryInterface, RememberingInterface
{
    use ArrayStorageTrait;

    /**
     * @var PackagesRepositoryInterface
     */
    protected PackagesRepositoryInterface $repository;

    protected ?array $meta = null;

    protected ?array $packages = null;

    public function __construct(PackagesRepositoryInterface $repository, string $storage_path = null)
    {
        $this->repository = $repository;
        if ($storage_path) {
            $this->setStoragePath($storage_path);
        }
    }

    public function getPackageConfig(string $id): ?PackageConfig
    {
        if ($this->has($id)) {
            return new PackageConfig($this->get($id));
        }
        return null;
    }

    public function has($id): bool
    {
        return isset($this->getIds()[$id]);
    }

    public function getIds(): array
    {
        return $this->getMeta()['ids'];
    }

    /**
     * @return array = ['ids'=>[],'bootstraps' =>[]];
     */
    protected function getMeta(): array
    {
        if (null === $this->meta) {
            $this->meta = $this->remember('packages_meta.php', function () {
                $this->repository->load();
                return [
                    'ids' => $this->repository->getIds(),
                    'bootstraps' => $this->repository->getBootstraps(),
                    'handlers' => $this->repository->getEventsHandlers(),

                ];
            });
        }

        return $this->meta;
    }

    /**
     * @param string $id
     * @return array
     * @throws \Exception
     */
    public function get(string $id): array
    {
        $packages = $this->all();
        if (!isset($this->getIds()[$id])) {
            throw new \Exception("Package [$id] not found!");
        }
        return $packages[$id];
    }

    public function all(): array
    {
        if (null === $this->packages) {
            $this->packages = $this->remember('packages_data.php', function () {
                $this->repository->load();
                return $this->repository->all();
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

    public function getEventsHandlers(): array
    {
        return $this->getMeta()['handlers'];
    }

    public function load(): void
    {
        /**
         * @see LazyPackagesDecorator::getMeta()
         * @see LazyPackagesDecorator::all()
         */
    }

}