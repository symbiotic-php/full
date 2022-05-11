<?php


namespace Symbiotic\Filesystem;

use Symbiotic\Container\DIContainerInterface;
use Symbiotic\Filesystem\Adapter\Local;



class FilesystemManager implements FilesystemManagerInterface
{
    /**
     * The application instance.
     *
     * @var \Symbiotic\Core\Core|DIContainerInterface
     */
    protected $app;

    /**
     * The array of resolved filesystem drivers.
     *
     * @var array
     */
    protected $disks = [];

    /**
     * The registered custom driver creators.
     *
     * @var array
     */
    protected $customCreators = [];

    /**
     * Create a new filesystem manager instance.
     *
     * @param    $app
     * @return void
     */
    public function __construct(DIContainerInterface $app)
    {
        $this->app = $app;
    }


    /**
     * Get a filesystem instance.
     *
     * @param  string  $name
     * @return FilesystemInterface
     */
    public function disk(string $name = null)
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->disks[$name] = $this->get($name);
    }


    /**
     * Attempt to get the disk from the local cache.
     *
     * @param  string  $name
     * @return FilesystemInterface
     */
    protected function get($name)
    {
        return $this->disks[$name] ?? $this->resolve($name);
    }

    /**
     * Resolve the given disk.
     *
     * @param  string  $name
     * @return FilesystemInterface
     *
     * @throws \InvalidArgumentException
     */
    protected function resolve($name)
    {
        $config = $this->getConfig($name);

        if (isset($this->customCreators[$config['driver']])) {
            return $this->callCustomCreator($config);
        }
        $driverMethod = 'create'.ucfirst($config['driver']).'Driver';

        if (\is_callable([$this, $driverMethod])) {
            return $this->{$driverMethod}($config);
        } else {
            throw new \InvalidArgumentException("Driver [{$config['driver']}] is not supported.");
        }
    }

    /**
     * Call a custom driver creator.
     *
     * @param  array  $config
     * @return FilesystemInterface
     */
    protected function callCustomCreator(array $config)
    {
        return $this->customCreators[$config['driver']]($this->app, $config);
    }

    /**
     * Create an instance of the local driver.
     *
     * @param  array  $config
     * @return FilesystemInterface
     */
    public function createLocalDriver(array $config)
    {
        return new Local($config['root'] ?? $this->app->getBasePath(), LOCK_EX, $config['permissions'] ?? []);
    }



    /**
     * Set the given disk instance.
     *
     * @param  string  $name
     * @param  mixed  $disk
     * @return $this
     */
    public function set(string $name, $disk)
    {
        $this->disks[$name] = $disk;

        return $this;
    }

    /**
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getDisks():array
    {
        return $this->app->get('config::filesystems.disks',[]);//todo !
    }

    /**
     * Get the filesystem connection configuration.
     *
     * @param  string $name
     * @return array
     */
    protected function getConfig(string $name)
    {
        return $this->app->get('config::filesystems.disks.'.$name);//todo
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app->get('config::filesystems.default');
    }

    /**
     * Get the default cloud driver name.
     *
     * @return string
     */
    public function getDefaultCloudDriver()
    {
        return $this->app['config::filesystems.cloud'];
    }

    /**
     * Unset the given disk instances.
     *
     * @param  array|string  $disk
     * @return $this
     */
    public function forgetDisk($disk)
    {
        foreach ((array) $disk as $diskName) {
            unset($this->disks[$diskName]);
        }

        return $this;
    }

    public function getDrivers(): array
    {
       return array_keys($this->customCreators);
    }

    /**
     * Register a custom driver creator Closure.
     *
     * @param  string    $driver
     * @param  \Closure  $callback
     * @return $this
     */
    public function addDriver(string $driver, \Closure $callback)
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->disk(), $method], $parameters);
    }
}

