<?php

namespace Symbiotic\Http\Kernel;


use Symbiotic\Core\{CoreInterface, BootstrapInterface, HttpKernelInterface};


class Bootstrap implements BootstrapInterface
{
    public function bootstrap(CoreInterface $app): void
    {
        $app->singleton(HttpKernelInterface::class, HttpKernel::class);
        $app->addRunner(new HttpRunner($app));
    }
}