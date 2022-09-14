<?php

declare(strict_types=1);

namespace Symbiotic\Packages;

use Symbiotic\Filesystem\ArrayStorageTrait;
use Symbiotic\Cache\RememberingInterface;


class LazyPackagesDecorator implements PackagesRepositoryInterface, RememberingInterface
{
    use ArrayStorageTrait;

    /**
     * @var PackagesRepositoryInterface
     */
    protected PackagesRepositoryInterface $repository;

    /**
     * @var array|null
     */
    protected ?array $meta = null;

    /**
     * @var array|null
     */
    protected ?array $packages = null;

    /**
     * @param PackagesRepositoryInterface $repository
     * @param string|null                 $storage_path
     *
     * @throws \Symbiotic\Filesystem\NotExistsException
     * @todo: Can transmit CacheInterface?
     *
     */
    public function __construct(PackagesRepositoryInterface $repository, string $storage_path = null)
    {
        $this->repository = $repository;
        if ($storage_path) {
            $this->setStoragePath($storage_path);
        }
    }

    /**
     * @param string $id
     *
     * @return PackageConfig|null
     * @throws
     */
    public function getPackageConfig(string $id): ?PackageConfig
    {
        if ($this->has($id)) {
            return new PackageConfig($this->get($id));
        }
        return null;
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->getIds()[$id]);
    }

    public function getIds(): array
    {
        return $this->getMeta()['ids'];
    }

    /**
     * @return array = ['ids'=>[],'bootstraps' =>[]];
     *
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
     *
     * @return array
     * @throws PackagesException
     */
    public function get(string $id): array
    {
        $packages = $this->all();
        if (!isset($this->getIds()[$id])) {
            throw new PackagesException("Package [$id] not found!");
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

    /**
     * @param PackagesLoaderInterface $loader
     *
     * @return void
     */
    public function addPackagesLoader(PackagesLoaderInterface $loader): void
    {
        $this->repository->addPackagesLoader($loader);
    }

    /**
     * @param array $config
     *
     * @return void
     */
    public function addPackage(array $config): void
    {
        $this->repository->addPackage($config);
    }

    /**
     * @return array
     */
    public function getBootstraps(): array
    {
        return $this->getMeta()['bootstraps'];
    }

    /**
     * @return array|array[]
     */
    public function getEventsHandlers(): array
    {
        return $this->getMeta()['handlers'];
    }

    /**
     * @return void
     */
    public function load(): void
    {
        /**
         * @see LazyPackagesDecorator::getMeta()
         * @see LazyPackagesDecorator::all()
         */
    }
}