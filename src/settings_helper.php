<?php

namespace _S;

use Symbiotic\Packages\PackageConfig;
use Symbiotic\Packages\PackagesRepositoryInterface;
use Symbiotic\Settings\SettingsInterface;
use Symbiotic\Settings\SettingsRepositoryInterface;

/**
 * @param string $package_id
 *
 * @return SettingsInterface
 * Будьте внимательны, всегда создается новый объект, лучше из приложения поучайте $app['settings']
 */
function settings(string $package_id): SettingsInterface
{
    $settings = core(SettingsInterface::class, [uniqid() => ''/* костыль для создания пустого объекта*/]);

    /**
     * Добавляем настройки пакета по умолчанию
     *
     * @var PackageConfig|null $package
     */
    $package = core(PackagesRepositoryInterface::class)->getPackageConfig($package_id);

    if ($package && $package->has('settings')) {
        $default_settings = $package->get('settings');
        $settings->setMultiple(\is_array($default_settings) ? $default_settings : []);
    }

    /**
     * Добавляем текущие настройки
     *
     * @var SettingsRepositoryInterface $repository
     */
    $repository = core(SettingsRepositoryInterface::class);
    if ($repository->has($package_id)) {
        $actual_settings = $repository->get($package_id);
        $settings->setMultiple(\is_iterable($actual_settings) ? $actual_settings : []);
    }

    return $settings;

}