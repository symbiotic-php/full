<?php
$basePath = dirname(__DIR__,4);
return [
    'debug' => false,
    'symbiosis' => true, // Режим симбиоза, если включен и фреймворк не найдет обработчик,
                         // то он ничего не вернет и основной фреймворк смодет сам обработать запрос
    'default_host' => 'localhost',// для консоли , но ее пока нет
    'uri_prefix' => 'symbiotic', // Префикс в котором работет фреймворк, если пустой то работае от корня
    'base_path' => $basePath, // базовая папка проекта
    'assets_prefix' => '/assets',
    'storage_path' =>  $basePath . '/storage', // Если убрать то кеш отключится
    'packages_settlements' => true,// разрешает адреса пакетов по ID приложения /FW/APP_ID/ROUTE
    'packages_paths' => [
        $basePath . '/vendor', // Папка для приложений
    ],
    'bootstrappers' => [
        \Symbiotic\Develop\Bootstrap\DebugBootstrap::class,/// debug only
        \Symbiotic\Core\Bootstrap\EventBootstrap::class,
        \Symbiotic\Filesystem\Bootstrap::class,
        \Symbiotic\SimpleCacheFilesystem\Bootstrap::class,
        \Symbiotic\Settings\SettingsBootstrap::class,
        \Symbiotic\Packages\PackagesLoaderFilesystemBootstrap::class,
        \Symbiotic\Packages\PackagesBootstrap::class,
        \Symbiotic\Packages\ResourcesBootstrap::class,
        \Symbiotic\Apps\Bootstrap::class,
        \Symbiotic\Http\Bootstrap::class,
        \Symbiotic\Http\Kernel\Bootstrap::class,
        \Symbiotic\Routing\SettlementsPreloadMiddlewareBootstrap::class,
        \Symbiotic\View\Blade\Bootstrap::class,
    ],
    'providers' => [
        \Symbiotic\Http\Cookie\CookiesProvider::class,
        \Symbiotic\Routing\SettlementsRoutingProvider::class,
        \Symbiotic\Routing\CacheRoutingProvider::class,
        \Symbiotic\Session\NativeProvider::class,
        \Symbiotic\Auth\Provider::class,
    ],
    'providers_exclude' => [
        \Symbiotic\Routing\Provider::class,
    ],
];