<?php

namespace Symbiotic\Routing;

use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};
use Symbiotic\Core\{CoreInterface, ProvidersRepository};

/**
 * Class Settlements
 * @package Symbiotic\Services
 *
 */
class KernelPreloadFindRouteMiddleware implements MiddlewareInterface
{

    protected CoreInterface $core;

    public function __construct(CoreInterface $core)
    {
        $this->core = $core;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $app = $this->core;

        $providers = [
            CacheRoutingProvider::class => null,
            SettlementsRoutingProvider::class => null
        ];
        foreach ($providers as $class => $v) {
            if (class_exists($class)) {
                $providers[$class] = $app->register(new $class($app));
            } else {
                unset($providers[$class]);
            }
        }
        foreach ($providers as $class => $v) {
            $v->boot();
        }
        /// реально можно отменить провайдеры из посредника? вот это прикол
        $app[ProvidersRepository::class]->exclude(array_keys($providers));

        $path = $request->getUri()->getPath();
        $route = $this->core['router']->match($request->getMethod(), $path);

        if ($route) {
            /**
             * @used-by \Symbiotic\Http\Kernel\RoutingHandler::handle()
             */
            $app['route'] = $route;
            return $handler->handle($request);
        } else {
            $app['destroy_response'] = true;
            return \_S\response(404, new \Exception('Route not found for path [' . $path . ']', 7623));
        }
    }
}