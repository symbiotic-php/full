<?php

namespace Symbiotic\Packages;

use Symbiotic\Core\CoreInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Class PackagesRepository
 * @package Symbiotic\Apps
 * @property  CoreInterface|array $app  = [
 *       'config' => new \Symbiotic\Config(),
 *       'router' => new \Symbiotic\Contracts\Routing\Router(),
 *       'apps' => new \Symbiotic\Contracts\Appss\AppsRepository(),
 *       'events' => new \Symbiotic\Event\DispatcherInterface(),
 *       'listeners' => new \Symbiotic\Event\ListenersInterface(),
 * ]
 */
class PackagesRepository implements PackagesRepositoryInterface
{
    /**
     * @var PackagesLoaderInterface[]
     */
    protected $loaders = [];

    protected array $items = [];

    protected $loaded = false;

    protected $ids = [];

    protected $bootstraps = [];

    public function addPackagesLoader(PackagesLoaderInterface $loader): void
    {
        $this->loaders[] = $loader;
    }

    /**
     * @param array $config
     * @return void
     */
    public function addPackage(array $config): void
    {
        $app = isset($config['app']) ? $config['app'] : null;

        // if modules are supported
        if (is_array($app)) {
            if (!isset($app['id']) && isset($config['id'])) {
                $app['id'] = $config['id'];
            } else {
                $config['id'] = $app['id'] = self::getAppId($app);
            }
            $config['app'] = $app;
        }
        $id = isset($config['id']) ? $config['id'] : \count($this->items);
        $this->ids[$id] = $id;
        $this->items[$id] = $config;
        if (!empty($config['bootstrappers'])) {
            $this->bootstraps = array_merge($this->bootstraps, (array)$config['bootstrappers']);
        }
    }


    public function getBootstraps(): array
    {
        return $this->bootstraps;
    }

    public function has($id): bool
    {
        return isset($this->ids[$id]);
    }

    public function get($key): array
    {
        return $this->items[$key];
    }

    /**
     * @return array
     */
    public function getPackages(): array
    {
        return $this->items;
    }

    public static function normalizeId(string $id)
    {
        return str_replace(['/', '-', '.'], ['_', '_', ''], \strtolower($id));
    }

    public static function getAppId(array $config)
    {
        if (!isset($config['id'])) {
            throw new \Exception('App id is required [' . \serialize($config) . ']!');
        }
        $name = $config['id'] = self::normalizeId($config['id']);
        $parent_app = $config['parent_app'] ?? null;
        if ($parent_app) {
            $config['parent_app'] = $parent_app = self::normalizeId($parent_app);
        }
        return $parent_app ? $parent_app . '.' . $name : $name;
    }

    public function getIds(): array
    {
        return $this->ids;
    }

    public function load(): void
    {
        if(!$this->loaded) {
            foreach ($this->loaders as $loader) {
                $loader->load($this);
            }
            $this->loaded = true;
        }
    }
}