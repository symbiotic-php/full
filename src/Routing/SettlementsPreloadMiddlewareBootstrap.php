<?php

namespace Symbiotic\Routing;



use Symbiotic\Core\BootstrapInterface;
use Symbiotic\Core\CoreInterface;
use Symbiotic\Http\Kernel\PreloadKernelHandler;

class SettlementsPreloadMiddlewareBootstrap implements BootstrapInterface
{
    public function bootstrap(CoreInterface $core): void
    {
        $core['listeners']->add(PreloadKernelHandler::class, function (PreloadKernelHandler $event) use ($core) {
            $event->append(new KernelPreloadFindRouteMiddleware($core));
        });
    }


}