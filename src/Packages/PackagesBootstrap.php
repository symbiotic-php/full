<?php

namespace Dissonance\Packages;

use Dissonance\Core\{AbstractBootstrap, CoreInterface};


class PackagesBootstrap extends AbstractBootstrap
{

    public function bootstrap(CoreInterface $app): void
    {
        $packages_class = PackagesRepositoryInterface::class;
        $app->singleton($packages_class, function (){return new PackagesRepository;});
        //$this->cached($app, $packages_class, PackagesRepository::class);
        $p = $app[$packages_class];
        $p->load();
        foreach ($p->getBootstraps() as $v) {
            if($v === get_class($this)) {
                continue;
            }
            $app->runBootstrap($v);
        }

    }
}
