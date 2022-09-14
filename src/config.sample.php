<?php

$basePath = dirname(__DIR__, 4);

$microConfig = $basePath . '/vendor/symbiotic/micro/src/config.sample.php';
if (file_exists($microConfig)) {
    $config = include $microConfig;
    if (!isset($config['providers_exclude'])) {
        $config['providers_exclude'] = [];
    }
    /**
     * Providers can be excluded from the main config only in the config itself
     * @see \Symbiotic\Core\ProvidersRepository::load()
     */
    $config['providers_exclude'][] = \Symbiotic\Routing\Provider::class;

    return $config;
}
throw new \Exception('The config from the micro package was not found!');