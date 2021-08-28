<?php
$basePath = dirname(__DIR__,4);
return [
    'debug' => true,
    'symbiosis' => false, // Режим симбиоза, если включен и фреймворк не найдет обработчик,
                         // то он ничего не вернет и основной фреймворк смодет сам обработать запрос
    'default_host' => 'localhost',// для консоли , но ее пока нет
    'uri_prefix' => 'dissonance', // Префикс в котором работет фреймворк, если пустой то работае от корня
    'base_path' => $basePath, // базовая папка проекта
    'assets_prefix' => '/assets',
    'storage_path' =>  $basePath . '/storage', // Если убрать то кеш отключится
    'packages_paths' => [
        $basePath . '/vendor', // Папка для приложений
    ],
    'bootstrappers' => [
        \Dissonance\Develop\Bootstrap\DebugBootstrap::class,/// debug only
        \Dissonance\Core\Bootstrap\EventBootstrap::class,
        \Dissonance\SimpleCacheFilesystem\Bootstrap::class,
        \Dissonance\Packages\PackagesLoaderFilesystemBootstrap::class,
        \Dissonance\Packages\PackagesBootstrap::class,
        \Dissonance\Packages\ResourcesBootstrap::class,
        \Dissonance\Apps\Bootstrap::class,
        \Dissonance\Http\Bootstrap::class,
        \Dissonance\Http\Kernel\Bootstrap::class,
        \Dissonance\View\Blade\Bootstrap::class,
    ],
    'providers' => [
        \Dissonance\Http\Cookie\CookiesProvider::class,
        \Dissonance\Routing\SettlementsRoutingProvider::class,
        \Dissonance\Routing\CacheRoutingProvider::class,
        \Dissonance\Session\NativeProvider::class,
    ],
    'providers_exclude' => [
        \Dissonance\Routing\Provider::class,
    ],
];