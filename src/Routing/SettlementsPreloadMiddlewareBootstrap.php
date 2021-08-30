<?php

namespace Symbiotic\Routing;



use Symbiotic\Core\BootstrapInterface;
use Symbiotic\Core\CoreInterface;
use Symbiotic\Http\Kernel\PreloadKernelHandler;

class SettlementsPreloadMiddlewareBootstrap implements BootstrapInterface
{
    public function bootstrap(CoreInterface $app): void
    {
        $app['listeners']->add(PreloadKernelHandler::class, function (PreloadKernelHandler $event) use ($app) {
            $event->append(new KernelPreloadFindRouteMiddleware($app));
        });
    }


}