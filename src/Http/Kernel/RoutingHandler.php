<?php


namespace Symbiotic\Http\Kernel;

use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;
use Symbiotic\Apps\AppsRepositoryInterface;
use Symbiotic\Core\CoreInterface;
use Symbiotic\Http\Middleware\{MiddlewaresDispatcher};
use function _S\event;


class RoutingHandler implements RequestHandlerInterface
{
    /**
     * @var CoreInterface
     */
    protected CoreInterface $core;

    public function __construct(CoreInterface $core)
    {
        $this->core = $core;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {

        $app = $this->core;
        /**
         * @var \Symbiotic\Routing\RouteInterface|null $route
         */
        $path = $request->getUri()->getPath();
        if ($this->core->has('route')) {
            /**
             * Для того чтобы не грузить все сервисы ядра,
             * сначала мы определяем роут если роута нет, нет смысла грузить ядро
             * Посредник дублирует поведение данного обработчика, но только при роутинге поселений
             * @see \Symbiotic\Routing\KernelPreloadFindRouteMiddleware::process()
             */
            $route = $app['route'];
        } else {
            $route = $app['router']->match($request->getMethod(), $path);
        }
        if ($route) {
            foreach ($route->getParams() as $k => $v) {
                $request = $request->withAttribute($k, $v);
            }

            /**
             * @todo наверно надо перенести отработку в {@see RouteHandler::handle()} после загрузки самого приложения
             */
            $middlewares = $route->getMiddlewares();
            $action = $route->getAction();
            if (isset($action['app'])) {
                $app[AppsRepositoryInterface::class]->getBootedApp($action['app']);
            }
            if (!empty($middlewares)) {
                $middlewares = $app[MiddlewaresDispatcher::class]->factoryCollection($middlewares);
            } else {
                $middlewares = [];
            }
            return event(new RouteMiddlewares($middlewares))->process($request, new RouteHandler($app, $route));
        } else {
            $app['destroy_response'] = true;
            return \_S\response(404, new \Exception('Route not found for path [' . $path . ']', 7623));
        }
    }

}