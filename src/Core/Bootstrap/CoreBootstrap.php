<?php

namespace Symbiotic\Core\Bootstrap;


use Symbiotic\Core\BootstrapInterface;
use Symbiotic\Core\Config;
use Symbiotic\Core\CoreInterface;
use Symbiotic\Core\Events\CacheClear;
use Symbiotic\Core\View\View;


class CoreBootstrap implements BootstrapInterface
{
    /**
     * @param CoreInterface $core
     */
    public function bootstrap(CoreInterface $core): void
    {
        $core->singleton(Config::class, function ($app) {
            return new Config($app['bootstrap_config']);
        }, 'config');


        View::setContainer($core);
        // Env settings
        $console_running_key = 'APP_RUNNING_IN_CONSOLE';
        if ((isset($_ENV[$console_running_key]) && $_ENV[$console_running_key] === 'true') ||
            \in_array(\php_sapi_name(), ['cli', 'phpdbg'])) {
            $core['env'] = 'console';
        } else {
            $core['env'] = 'web';
        }

        \date_default_timezone_set($core('config::core.timezone', 'UTC'));
        \mb_internal_encoding('UTF-8');


        $storage_path = $core('config::storage_path');
        $cache_path = $core('config::cache_path');

        if ($storage_path) {
            $core['storage_path'] = $storage_path = \rtrim($storage_path, '\\/');
            if (empty($cache_path)) {
                $cache_path = $storage_path . '/cache/';
            }
        }

        if (!empty($cache_path)) {
            $core['cache_path'] = $cache_path;
            $core['cache_path_core'] = rtrim($cache_path, '\\/') . '/core';
        }

        $start_bootstrappers = $core->get('config::bootstrappers');
        if (\is_array($start_bootstrappers)) {
            foreach ($start_bootstrappers as $class) {
                $core->runBootstrap($class);
            }
        }
        // При очистке
        $core['listeners']->add(CacheClear::class, function (CacheClear $event) use ($core) {
            if ($event->getPath() === 'all' || $event->getPath() === 'core') {
                $core['cache_cleaned'] = true;
            }
        });


    }
}