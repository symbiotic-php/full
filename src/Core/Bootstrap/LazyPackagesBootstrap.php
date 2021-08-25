<?php

namespace Dissonance\Core\Bootstrap;

use Dissonance\Core\AbstractBootstrap;
use Dissonance\Core\CoreInterface;
use Dissonance\Packages\LazyPackagesDecorator;
use Dissonance\Packages\PackagesRepositoryInterface;

class LazyPackagesBootstrap extends AbstractBootstrap {

    public function bootstrap(CoreInterface $app): void
    {
        $app->extend(PackagesRepositoryInterface::class, function (PackagesRepositoryInterface $repo, $app) {
            return new LazyPackagesDecorator($repo, $app('cache_path_core'));
        });
    }
}