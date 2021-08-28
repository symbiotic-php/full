<?php
$basePath = dirname(__DIR__,4);

\Symbiotic\Core\Autoloader::register(false, [$basePath . '/modules'],
    $basePath . '/storage/cache/core'// Если не кешировать, будет долго искать при большом количестве автозагрузки провайдеров и бутстрапов! Там говнокод!
);


$config = include __DIR__.'/config.sample.php';

// Режим совместной работы с другим фреймворком
//$config['symbiotic'] = true;

// Можно и в корне запускать
//$config['uri_prefix'] = '';

$cache = new Symbiotic\SimpleCacheFilesystem\SimpleCache($basePath . '/storage/cache/core');
/// Загружаем  ядро
$app = (new \Symbiotic\Core\ContainerBuilder($cache))
    ->buildCore($config);

/// Запускаем
$app->run();

/**
 * Дальше может идти запуск вашего фреймворка
 * при режиме симбиоза Symbiotic обрабаытвает только свои роуты
 */

