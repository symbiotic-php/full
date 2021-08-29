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
        if($this->app->has('route')) {
            /**
             * Для того чтобы не грузить все сервисы ядра,
             * сначала мы определяем роут если роута нет, нет смысла грузить ядро
             * Посредник дублирует поведение данного обработчика, но только пр роутинге поселений
             * @see \Symbiotic\Routing\KernelPreloadFindRouteMiddleware::process()
             */
            $route = $app['route'];
        } else {
            $path = $request->getUri()->getPath();
            $route = $app['router']->match($request->getMethod(), $path);
        }
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