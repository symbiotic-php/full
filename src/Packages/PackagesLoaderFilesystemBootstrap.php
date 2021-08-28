<?php

namespace Symbiotic\Packages;

use Symbiotic\Core\CoreInterface;
use Symbiotic\Core\BootstrapInterface;

class PackagesLoaderFilesystemBootstrap implements BootstrapInterface
{

    public function bootstrap(CoreInterface $app): void
    {
       $app->afterResolving(PackagesRepositoryInterface::class, function(PackagesRepositoryInterface $repository) use($app) {
            $repository->addPackagesLoader(
                new PackagesLoaderFilesystem($app->get('config::packages_paths'))
            );
       });
    }
}
