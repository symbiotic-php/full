<?php


namespace Symbiotic\Http\Kernel;

use Symbiotic\Http\Middleware\ {MiddlewaresCollection, MiddlewaresDispatcher};
use Psr\Http\Message\ {ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;
use Symbiotic\Core\CoreInterface;


class RoutingHandler implements RequestHandlerInterface
{
    /**
     * @var CoreInterface
     */
    protected $app;

    public function __construct(CoreInterface $app)
    {
        $this->app = $app;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {

        $app = $this->app;
        /**
         * @var \Symbiotic\Routing\RouteInterface|null $route
         */
        $path = $request->getUri()->getPath();
        $route = $app['router']->match($request->getMethod(), $path);
        if ($route) {
            $middlewares = $route->getMiddlewares();
            if (!empty($middlewares)) {
                $app[MiddlewaresDispatcher::class]->factoryCollection($middlewares);
            }
            return (new MiddlewaresCollection($middlewares))->process($request, new RouteHandler($app, $route));
        } else {
            $app['destroy_response'] = true;
            return \_DS\response(404, new \Exception('Route not found for path [' . $path . ']', 7623));
        }
    }

}