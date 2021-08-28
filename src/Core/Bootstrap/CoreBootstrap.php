<?php

namespace Symbiotic\Core\Bootstrap;


use Symbiotic\Core\BootstrapInterface;
use Symbiotic\Core\CoreInterface;
use Symbiotic\Core\Config;
use Symbiotic\Core\Events\CacheClear;
use Symbiotic\Core\View\View;


class CoreBootstrap implements BootstrapInterface
{
    /**
     * @param CoreInterface $app
     */
    public function bootstrap(CoreInterface $app): void
    {
        $app->singleton(Config::class, function ($app) {
            return new Config($app['bootstrap_config']);
        }, 'config');


        View::setContainer($app);
        // Env settings
        $console_running_key = 'APP_RUNNING_IN_CONSOLE';
        if ((isset($_ENV[$console_running_key]) && $_ENV[$console_running_key] === 'true') ||
            \in_array(\php_sapi_name(), ['cli', 'phpdbg'])) {
            $app['env'] = 'console';
        } else {
            $app['env'] = 'web';
        }

        \date_default_timezone_set($app('config::core.timezone', 'UTC'));
        \mb_internal_encoding('UTF-8');


        $storage_path = $app('config::storage_path');

        if ($storage_path) {
            $app['storage_path'] = $storage_path = \rtrim($storage_path, '\\/');
            $app['cache_path'] = $storage_path . '/cache/';
            $app['cache_path_core'] = $storage_path . '/cache/core';
        }
        $start_bootstrappers = $app->get('config::bootstrappers');
        if (\is_array($start_bootstrappers)) {
            foreach ($start_bootstrappers as $class) {
                $app->runBootstrap($class);
            }
        }
        // При очистке
        $app['listeners']->add(CacheClear::class, function (CacheClear $event) use ($app) {
            if ($event->getPath() === 'all' || $event->getPath() === 'core') {
                $app['cache_cleaned'] = true;
            }
        });


    }
}