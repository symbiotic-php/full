<?php

namespace Dissonance\Http\Kernel;


use Dissonance\Core\{CoreInterface, BootstrapInterface, HttpKernelInterface};


class Bootstrap implements BootstrapInterface
{
    public function bootstrap(CoreInterface $app): void
    {
        $app->singleton(HttpKernelInterface::class, HttpKernel::class);
        $app->addRunner(new HttpRunner($app));
    }
}