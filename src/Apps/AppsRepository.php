<?php

namespace Symbiotic\Apps;

use function _DS\app;


class AppsRepository implements AppsRepositoryInterface
{

    /**
     * @var ApplicationInterface[]
     */
    protected array $apps = [];
    /**
     * @var array
     */
    protected array $apps_plugins = [];

    /**
     * @var array
     */
    protected array $apps_config = [];

    /**
     * @var array
     */
    protected array $disabled_apps = [];

    /**
     * @param array $ids
     */
    public function disableApps(array $ids)
    {
        $this->disabled_apps = array_merge($this->disabled_apps, array_combine($ids, $ids));
    }

    /**
     *
     * Фреймворк принимает пакеты композера в качестве приложений и компонентов или просто архив зарегистрированный
     * в системе как приложение(будет сайт).
     * Система предполагает многоуровневую зависимость приложений и пакетов, как пример: есть приложение визуального редактора Tiny,
     * для него есть плагин для редактирования изображений , для редактора изображений есть фича(кнопка), например размытие  лиц на фото.
     *
     * @param array $config = [
     *     'id' => 'app_id_string', // Register short app id or use composer package name
     *     'name' => 'App title',
     *     'parent_app' => 'parent_app_id', //  Parent app id or package name
     *     'description' => 'App description....',
     *     'routing' => '\\Symbiotic\\App\\Core\\Routing', // class name implements {@see \Symbiotic\Routing\AppRoutingInterface}
     *     'controllers_namespace' => '\\Symbiotic\\App\\Core\\Controllers', // Your base controllers namespace
     *     'version' => '1.0.2',
     *     'providers' => [    // Providers of your app
     *       '\\Symbiotic\\App\\Core\\Providers\\FilesProvider',
     *       '\\Symbiotic\\App\\Core\\Providers\\AppsUpdaterProvider',
     *      ],
     *
     *     // .... and your advanced params
     * ]
     * @return void
     * @throws
     */
    public function addApp(array $config)
    {
        if (empty($config['id'])) {
            throw  new \Exception('Empty app id!');
        }
        $id = $config['id'];
        $this->apps_config[$id] = $config;
        $parent_app = $config['parent_app'] ?? null;
        if ($parent_app) {
            $this->apps_plugins[$parent_app][$id] = 1;
        }
    }


    /**
     * @param string $id
     * @return ApplicationInterface|null
     * @throws \Exception
     */
    public function get(string $id): ?ApplicationInterface
    {
        if (isset($this->apps[$id])) {
            return $this->apps[$id];
        }
        if ($config = $this->getConfig($id)) {
            $app = app(
                (isset($config['app_class'])) ? $config['app_class'] : Application::class,
                ['app' => isset($config['parent_app']) ? $this->get($config['parent_app']) : app(), 'config' => $config]
            );
            return $this->apps[$id] = $app;
        }
        throw new \Exception("Application with id [$id] is not exists!");
    }

    /**
     * @param string $id
     * @return AppConfigInterface|null
     */
    public function getConfig(string $id): ?AppConfigInterface
    {
        return isset($this->apps_config[$id]) ?  new AppConfig($this->apps_config[$id]) : null;
    }

    /**
     * @param string $id
     * @return ApplicationInterface|null
     * @throws \Exception
     */
    public function getBootedApp(string $id): ?ApplicationInterface
    {
        $app = $this->get($id);
        if ($app) {
            $app->bootstrap();
        }
        return $app;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->apps_config[$id]);
    }

    /**
     * @return array|string[]
     */
    public function getIds():array
    {
        return array_keys($this->apps_config);
    }

    /**
     * @return array[]
     *
     * @throws \Exception
     */
    public function enabled(): array
    {
        return $this->all();
    }

    /**
     * @param string $id
     * @return array|[string]
     */
    public function getPluginsIds(string $id): array
    {
        return (isset($this->apps_plugins[$id])) ? array_keys($this->apps_plugins[$id]) : [];
    }

    /**
     * @return array|array[]
     */
    public function all(): array
    {
        return $this->apps_config;
    }
}