<?php

namespace Symbiotic\Settings;

use Symbiotic\Apps\ApplicationInterface;
use Symbiotic\Core\BootstrapInterface;
use Symbiotic\Core\Config;
use Symbiotic\Core\CoreInterface;
use Symbiotic\Filesystem\FilesystemManagerInterface;
use function _S\collect;
use function _S\settings;

class SettingsBootstrap implements BootstrapInterface
{
    public function bootstrap(CoreInterface $core): void
    {
        // Хранилище настроек
        $core->bind(SettingsStorageInterface::class, function ($core) {
            return new SettingsStorageFilesystem(rtrim($core['storage_path'], '/\\') . '/settings');
        });
        // Репозиторий
        $core->singleton(SettingsRepositoryInterface::class, SettingsRepository::class);

        /** Биндим для создания через ядро {@used-by \_S\settings()}  **/
        $core->bind(SettingsInterface::class, Settings::class);
        /**
         * после создания класса приложения, биндим ему класс настроек всем подряд, даже если нет настроек
         */
        $core->afterResolving(ApplicationInterface::class, function (ApplicationInterface $application) use ($core) {
            $application->alias(SettingsInterface::class, Settings::class);
            $application->singleton(SettingsInterface::class, function ($app) {
                return settings($app->getId());
            });
        });


        /**
         * Для получения насторек пакета без приложения  в виде массива  можно напрямую обратиться к репозиторию
         *   $repository = $core[SettingsRepositoryInterface::class];
         *   $package_settings = $repository->get($package_id);
         *
         * @see \_S\settings()
         *
         *
         */
        /**
         * TODO: Это должно выполняться после бутстрапа!!!!!!!!!
         * это необходимо для правильного выбора хранилища, оно может позже забиндится в ядро плагином {@see SettingsStorageInterface}
         */
        if ($core[SettingsRepositoryInterface::class]->has('core')) {
            /**
             * @var SettingsInterface $core_settings
             * @var Config $config
             */
            $core_settings = collect($core[SettingsRepositoryInterface::class]->get('core'));
            $config = $core['config'];

            if ($core_settings->has('uri_prefix')) {
                $config->set('uri_prefix', $core_settings['uri_prefix']);
            }
            foreach (['default_host','backend_prefix','assets_prefix'] as $v) {
                if (!empty($core_settings[$v])) {
                    $config->set($v, $core_settings[$v]);
                }
            }
            foreach (['debug','packages_settlements','symbiosis'] as $v) {
                if ($core_settings->has($v)) {
                    $config->set($v, (bool)$core_settings[$v]);
                }
            }

            $core->instance(SettingsInterface::class, $core[SettingsRepositoryInterface::class]->get('core'));
        }

        // append filesystems
        if ($core[SettingsRepositoryInterface::class]->has('filesystems')) {
            /**
             * @var SettingsInterface $core_settings
             * @var Config $config
             */
            $filesystems_settings = $core[SettingsRepositoryInterface::class]->get('filesystems');
            if(!empty($filesystems_settings) && is_array($filesystems_settings)) {
                $core->afterResolving(FilesystemManagerInterface::class, function (FilesystemManagerInterface $manager) use ($filesystems_settings, $config) {

                    $filesystems = $config->get('filesystems', []);
                    if(!isset($filesystems['disks'])) {
                        $filesystems['disks'] = [];
                    }
                    $filesystems['disks'] = array_merge($filesystems['disks'], $filesystems_settings);
                    $config->set('filesystems', $filesystems);

                });
            }

        }
    }
}