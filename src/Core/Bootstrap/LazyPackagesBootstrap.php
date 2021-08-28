<?php

namespace Symbiotic\Core\Bootstrap;

use Symbiotic\Core\AbstractBootstrap;
use Symbiotic\Core\CoreInterface;
use Symbiotic\Packages\LazyPackagesDecorator;
use Symbiotic\Packages\PackagesRepositoryInterface;

class LazyPackagesBootstrap extends AbstractBootstrap {

    public function bootstrap(CoreInterface $app): void
    {
        $app->extend(PackagesRepositoryInterface::class, function (PackagesRepositoryInterface $repo, $app) {
            return new LazyPackagesDecorator($repo, $app('cache_path_core'));
        });
    }
}