<?php

namespace Symbiotic\Packages;

use Symbiotic\Core\CoreInterface;

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
    protected array $loaders = [];

    protected array $items = [];

    protected bool $loaded = false;

    protected array $ids = [];

    /**
     * @var array
     */
    protected array $bootstraps = [];

    /**
     * Core events handlers
     *
     * @var array [\Core\EventClass => ['\my\HandlerClass',...]]
     */
    protected array $handlers = [];


    /**
     * Добавлять можно только из загрузчиков до {@see PackagesBootstrap} в кончиге ядра
     * @param PackagesLoaderInterface $loader
     */
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
            // todo что - то слишком сложно!!!
            if (!isset($app['id']) && isset($config['id'])) {
                $app['id'] = self::normalizeId($config['id']);
            }
            if (isset($app['id'])) {
                $config['id'] = $app['id'] = self::getAppId($app);
            }
            $config['app'] = $app;
        }
        $id = isset($config['id']) ? $config['id'] : \md5(\serialize($config));
        $this->ids[$id] = $id;
        $this->items[$id] = $config;
        if (!empty($config['bootstrappers'])) {
            $this->bootstraps = array_merge($this->bootstraps, (array)$config['bootstrappers']);
        }

        if (isset($config['events'])) {
            $this->handlers[$id] = array_merge($config['events'], ['id' => $id]);
        }
    }

    public static function normalizeId(string $id)
    { //todo collision
        return str_replace(['/', '-', '.'], ['-', '_', '_'], \strtolower($id));
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


    /**
     * @see     addResolver
     * @used-by PackagesBootstrap::bootstrap()
     * @var array |\CLosure[]
     */
    /*  protected $config_resolvers = [];*/
    /**
     * @used-by PackagesBootstrap::bootstrap()
     *
     * @param \Closure $resolver
     *
     * @example function(array $package_config) : void { \_S\listen($package_config['run_event'], $package_config['run_handler']); };
     */
    /* public function addResolver(\Closure $resolver)
     {
         $this->config_resolvers[] = $resolver;
     }*/

    /*public function getResolvers()
    {
       return $this->config_resolvers;
    }*/

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->items;
    }

    public function getBootstraps(): array
    {
        return $this->bootstraps;
    }

    /**
     * Core listeners for events
     * @return array[] = ['Events\EventClassName' => ['\My\Handler1','\Other\Handler3'], //....]
     * @see addPackage()
     */
    public function getEventsHandlers(): array
    {
        $handlers = $this->handlers;
        usort($handlers, function ($a, $b) {
            $a_after = isset($a['after']) ? (array)$a['after'] : [];
            $b_after = isset($b['after']) ? (array)$b['after'] : [];
            if (in_array($b['id'], $a_after)) {
                return -1;
            }
            if (in_array($a['id'], $b_after)) {
                return 1;
            }
            return 0;
        });

        $result = [];
        foreach ($handlers as $package) {
            if (isset($package['handlers'])) {
                foreach (((array)$package['handlers']) as $event => $listeners) {
                    $event = trim($event, '\\');
                    if (!isset($result[$event])) {
                        $result[$event] = [];
                    }
                    $result[$event] = array_merge($result[$event], (array)$listeners);
                }
            }
        }

        return $result;
    }

    /**
     * @param string $id
     * @return PackageConfig|null
     * @throws \Exception
     */
    public function getPackageConfig(string $id): ?PackageConfig
    {
        if ($this->has($id)) {
            return new PackageConfig($this->get($id));
        }
        return null;
    }

    public function has($id): bool
    {
        return isset($this->ids[$id]);
    }

    /**
     * @param string $key
     * @return array
     *
     * @throws \Exception
     */
    public function get(string $key): array
    {
        if (!isset($this->items[$key])) {
            throw new \Exception("Package [$key] not found!");
        }
        return $this->items[$key];
    }

    public function getIds(): array
    {
        return $this->ids;
    }

    public function load(): void
    {
        if (!$this->loaded) {
            foreach ($this->loaders as $loader) {
                $loader->load($this);
            }
            $this->loaded = true;
        }
    }
}