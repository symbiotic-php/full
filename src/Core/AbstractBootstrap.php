<?php
namespace Dissonance\Core;

use Dissonance\Container\CachedContainerInterface;


abstract class AbstractBootstrap implements BootstrapInterface
{

    protected function cached($app, string $abstract, \Closure|string $concrete = null, string $alias = null)
    {
        $app instanceof CachedContainerInterface
            ? $app->cached($abstract, $concrete, $alias)
            : $app->singleton($abstract, $concrete, $alias);
    }
}