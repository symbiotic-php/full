<?php
namespace Symbiotic\Core;

use Symbiotic\Container\CachedContainerInterface;


abstract class AbstractBootstrap implements BootstrapInterface
{

    protected function cached($app, string $abstract, $concrete = null, string $alias = null)
    {
        $app instanceof CachedContainerInterface
            ? $app->cached($abstract, $concrete, $alias)
            : $app->singleton($abstract, $concrete, $alias);
    }
}