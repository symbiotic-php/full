<?php

namespace Symbiotic\Core;

use Symbiotic\Container\ServiceContainerInterface;
use Symbiotic\Packages\PackagesRepositoryInterface;

class ProvidersRepository
{
    const EXCLUDE = 1;
    const ACTIVE = 3;
    const DEFER = 5;
    /**
     * @var array
     * [class => bool (active flag),... ]
     */
    protected $providers = [];

    /**
     * @var array [serviceClassName => ProviderClassName]
     */
    protected $defer_services = [];

    protected $loaded = false;

    /**
     * @param string|string[] $items
     * @param int $flag
     */
    public function add(array $items, $flag = self::ACTIVE)
    {
        $providers = &$this->providers;
        foreach ($items as $v) {
            $v = ltrim($v, '\\');
            $providers[$v] = isset($providers[$v]) ? $providers[$v] | $flag : $flag;
        }
    }


    /**
     * @param string|string[] $items
     */
    public function exclude(array $items)
    {
        $this->add($items, self::EXCLUDE);
    }

    /**
     * @param array $items = [ProviderClasName => [Service1,Service2]]
     */
    protected function defer(array $items)
    {
        $providers = [];
        foreach ($items as $provider => $services) {
            $providers [] = $provider;
            foreach ($services as $v) {
                $this->defer_services[\ltrim($v)] = $provider;
            }
        }
        $this->add($providers, self::DEFER);
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->providers;
    }

    public function isDefer($service)
    {
        return isset($this->defer_services[\ltrim($service)]);
    }

    /**
     * @param ServiceContainerInterface $app
     * @param array $force_providers
     * @param array $force_exclude
     */
    public function load(ServiceContainerInterface $app, array $force_providers = [], array $force_exclude = [])
    {
        if (!$this->loaded) {
            foreach ($app[PackagesRepositoryInterface::class]->getPackages() as $config) {
                $this->add(isset($config['providers']) ? (array)$config['providers'] : []);
                $this->defer(isset($config['defer']) ? (array)$config['defer'] : []);
                $this->exclude(isset($config['providers_exclude']) ? (array)$config['providers_exclude'] : []);
            }
        }
        $this->exclude($force_exclude);
        foreach ($force_providers as $v) {
            $this->providers[ltrim($v, '\\')] = self::ACTIVE;
        }
        /**
         * @var ServiceProviderInterface $provider
         */
        foreach ($this->providers as $provider => $mask) {
            if (!($mask & (self::DEFER | self::EXCLUDE))) {
                $app->register($provider);
            }
        }
        $app->setDeferred($this->defer_services);
    }

    public function __wakeup()
    {
        $this->loaded = true;
    }

}