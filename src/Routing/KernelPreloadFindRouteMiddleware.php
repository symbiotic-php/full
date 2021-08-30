<?php

namespace Symbiotic\Routing;

use Psr\Http\Message\{ServerRequestInterface,ResponseInterface};
use Psr\Http\Server\{MiddlewareInterface,RequestHandlerInterface};
use Symbiotic\Core\{CoreInterface,ProvidersRepository};

/**
 * Class Settlements
 * @package Symbiotic\Services
 *
 */
class KernelPreloadFindRouteMiddleware implements MiddlewareInterface
{

    protected $core;

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
            return \_DS\response(404, new \Exception('Route not found for path [' . $path . ']', 7623));
        }
    }
}