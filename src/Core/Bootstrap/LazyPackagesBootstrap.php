<?php

declare(strict_types=1);

namespace Symbiotic\Core\Bootstrap;

use Symbiotic\Container\DIContainerInterface;
use Symbiotic\Core\AbstractBootstrap;
use Symbiotic\Core\CoreInterface;
use Symbiotic\Packages\LazyPackagesDecorator;
use Symbiotic\Packages\PackagesRepositoryInterface;


class LazyPackagesBootstrap extends AbstractBootstrap
{
    public function bootstrap(DIContainerInterface $core): void
    {
        $core->extend(
            PackagesRepositoryInterface::class,
            function (PackagesRepositoryInterface $repo, CoreInterface $app) {
                return new LazyPackagesDecorator($repo, $app('cache_path_core'));
            }
        );
    }
}