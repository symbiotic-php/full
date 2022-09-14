<?php

declare(strict_types=1);

namespace Symbiotic\Bootstrap;

use Symbiotic\Container\DIContainerInterface;
use Symbiotic\Core\BootstrapInterface;
use Symbiotic\Core\CoreInterface;
use Symbiotic\Http\Kernel\PreloadKernelHandler;
use Symbiotic\Routing\KernelPreloadFindRouteMiddleware;


class SettlementsPreloadMiddlewareBootstrap implements BootstrapInterface
{
    public function bootstrap(DIContainerInterface $core): void
    {
        /**
         * Determining the route before loading the kernel and providers
         */
        $core['listeners']->add(
            PreloadKernelHandler::class,
            static function (PreloadKernelHandler $event, CoreInterface $core) {
                $event->append(new KernelPreloadFindRouteMiddleware($core));
            }
        );
    }
}