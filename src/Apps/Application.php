<?php

namespace Symbiotic\Apps;

use Symbiotic\Container\{DIContainerInterface, SubContainerTrait, ServiceContainerTrait};


/**
 * Class Application
 * @package Symbiotic\App
 * @property AppConfigInterface $config
 * @property $this = [
 *     'config' => new AppConfig()
 * ]
 */
class Application implements ApplicationInterface
{
    use ServiceContainerTrait,
        SubContainerTrait;


    public function __construct(DIContainerInterface $app, AppConfigInterface $config = null)
    {

        $this->app = $app;
        $this->instance(AppConfigInterface::class, $config, 'config');
        $config_class = get_class($config);
        if ($config_class !== AppConfig::class) {
            $this->alias(AppConfigInterface::class, AppConfig::class);
        }

        $class = get_class($this);
        $this->dependencyInjectionContainer = $this;
        $this->instance($class, $this);
        $this->alias($class, ApplicationInterface::class);
        if ($class !== self::class) {
            $this->alias($class, self::class);
        }

    }

    public function getId(): string
    {
        return $this['config']->getId();
    }

    public function getAppName(): string
    {
        return $this['config']->getAppName();
    }

    public function getAppTitle(): string
    {
        return $this['config']->getAppName();
    }

    public function getRoutingProvider(): ?string
    {
        return $this['config']->getRoutingProvider();
    }

    public function hasParentApp(): bool
    {
        return $this['config']->hasParentApp();
    }

    public function getParentAppId(): ?string
    {
        return $this['config']->getParentAppId();
    }

    protected function getBootstrapCallback()
    {
        return function () {
            $this->registerProviders();
            $this->boot();
        };
    }

    /**
     * @param array|\Closure[]|null $bootstraps Подмодуль может передать свой загрузчик для правильной
     * последовательности загрузки зависимостей
     *
     */
    public function bootstrap(array $bootstraps = null): void
    {

        if (!is_array($bootstraps)) {
            $bootstraps = [];
        }
        if (!$this->booted) {
            $bootstraps[] = $this->getBootstrapCallback();
        }

        // Если есть родительский модуль передаем свой загрузчик
        if (!$this->booted && $parent_app = $this->getParentApp()) {
            $parent_app->bootstrap($bootstraps);
        } else {
            // Запускаем загрузку , начиная от самого корневого модуля
            foreach (array_reverse($bootstraps) as $boot) {
                $boot();
            }
        }

        $this->booted = true;
    }

    protected function registerProviders()
    {
        foreach ($this('config::providers', []) as $provider) {
            $this->register($provider);
        }
    }


    /**
     * @param string|null $path
     * @return string|null
     */
    public function getBasePath(string $path = null): ?string
    {
        $base = $this('config::base_path');
        return $base ? ($path ? $base . \_DS\DS . ltrim($path) : $base) : null;
    }

    /**
     * @return string|null
     * @deprecated
     */
    public function getAssetsPath(): ?string
    {
        return $this->getBasePath('assets');
    }

    /**
     * @return string|null
     * @deprecated
     */
    public function getResourcesPath(): ?string
    {
        return $this->getBasePath('resources');
    }

    /**
     * @return ApplicationInterface|null
     */
    protected function getParentApp(): ?ApplicationInterface
    {
        return $this->hasParentApp() ? $this[AppsRepositoryInterface::class]->get($this->getParentAppId()) : null;
    }

}
 