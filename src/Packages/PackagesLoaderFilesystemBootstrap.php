<?php

namespace Symbiotic\Packages;

use Symbiotic\Core\BootstrapInterface;
use Symbiotic\Core\CoreInterface;

class PackagesLoaderFilesystemBootstrap implements BootstrapInterface
{

    public function bootstrap(CoreInterface $core): void
    {
        $core->afterResolving(PackagesRepositoryInterface::class, function (PackagesRepositoryInterface $repository) use ($core) {
            $repository->addPackagesLoader(
                new PackagesLoaderFilesystem($core->get('config::packages_paths'))
            );
        });
    }
}
