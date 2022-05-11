<?php

namespace Symbiotic\Http\Kernel;


use Symbiotic\Core\{CoreInterface, BootstrapInterface, HttpKernelInterface};


class Bootstrap implements BootstrapInterface
{
    public function bootstrap(CoreInterface $core): void
    {
        $core->singleton(HttpKernelInterface::class, HttpKernel::class);
        $core->addRunner(new HttpRunner($core));
    }
}