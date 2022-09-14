<?php

declare(strict_types=1);

namespace Symbiotic\Routing;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};
use Symbiotic\Core\{CoreInterface, HttpKernelInterface, ProvidersRepository};
use Symbiotic\Container\CloningContainer;


class KernelPreloadFindRouteMiddleware implements MiddlewareInterface, CloningContainer
{

    /**
     * @var CoreInterface
     */
    protected CoreInterface $core;

    /**
     * Routing providers for register
     * @var array
     */
    protected array $routingProviders = [
        CacheRoutingProvider::class => null,
        SettlementsRoutingProvider::class => null
    ];

    public function __construct(CoreInterface $core)
    {
        $this->core = $core;
    }

    /**
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $core = $this->core;

        // Routing providers
        if (!$core->isBooted()) {
            $core->instance(ServerRequestInterface::class, $request);
            // We do not register disabled providers
            $exclude_providers = array_flip(
                array_map(
                    fn($v) => \ltrim($v, '\\'),
                    $core('config::providers_exclude', [])
                )
            );
            $providers = $this->routingProviders;
            foreach ($providers as $class => $v) {
                if (class_exists($class) && !isset($exclude_providers[$class])) {
                    $providers[$class] = $core->register($class);
                } else {
                    unset($providers[$class]);
                }
            }
            foreach ($providers as $provider) {
                $provider->boot();
            }
            /// реально можно отменить провайдеры из посредника? вот это прикол
            $core[ProvidersRepository::class]->exclude(array_keys($providers));
            $core['router']; // call factory router class
            $core->delete(ServerRequestInterface::class);
        }


        $path = $request->getUri()->getPath();
        $route = $core['router']->match($request->getMethod(), $path);

        if ($route) {
            /**
             * @used-by \Symbiotic\Http\Kernel\RoutingHandler::handle()
             */
            $core->instance(RouteInterface::class, $route);
            $core->setLive(RouteInterface::class);

            return $handler->handle($request);
        } else {
            $core['destroy_response'] = true;
            return $core(HttpKernelInterface::class)->response(
                404,
                new RouteNotFoundException(
                    'Route not found for path [' . $path . ']',
                    7623
                )
            );
        }
    }

    public function cloneInstance(?ContainerInterface $container): ?static
    {
        /**
         * @var CoreInterface $container
         */
        $new = clone $this;
        $new->core = $container;
        return $new;
    }
}