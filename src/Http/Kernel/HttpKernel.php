<?php

namespace Symbiotic\Http\Kernel;

use Symbiotic\Container\FactoryInterface;
use Symbiotic\Core\CoreInterface;

use Symbiotic\Core\HttpKernelInterface;
use Symbiotic\Http\Middleware\MiddlewaresCollection;
use Symbiotic\Http\Middleware\MiddlewaresDispatcher;

use Symbiotic\Core\View\View;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use function _DS\config;


class HttpKernel implements HttpKernelInterface
{
    /**
     * @var ContainerInterface|CoreInterface
     */
    protected $app;
    /**
     * @var string[]  Names of classes implements from {@uses \Symbiotic\Core\BootstrapInterface}
     */
    protected $bootstrappers = [];

    protected $mod_rewrite = null;


    public function __construct(ContainerInterface $container)
    {
        $this->app = $container;
        if ($container->has('config')) {
            $config = $container->get('config');
            $this->mod_rewrite = $config->has('mod_rewrite') ? $config->get('mod_rewrite') : null;
        }
    }


    /**
     * Запускает инициализацию ядра
     */
    public function bootstrap(): void
    {
        if (!$this->app->isBooted()) {
            $this->app->addBootstraps($this->bootstrappers);
            $this->app->bootstrap();
        }
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {

        /**
         * @var ContainerInterface|CoreInterface $app
         */
        $app = $this->app;


        $dispatcher = $app->instance(MiddlewaresDispatcher::class, new MiddlewaresDispatcher());
        $dispatcher->setDefaultCallback(function ($class) use ($app) {
            /**
             * @var ContainerInterface|FactoryInterface $app
             */
            return $app->make($class);
        });

        return (new MiddlewaresCollection(
                \_DS\event($dispatcher)
                    ->factoryGroup(MiddlewaresDispatcher::GROUP_GLOBAL)

                ))->process($request, $app->make(RoutingHandler::class));
    }


    /**
     * @param int $code
     * @param \Throwable |null $exception
     * @return ResponseInterface
     */
    public function response(int $code = 200, \Throwable $exception = null): ResponseInterface
    {
        $app = $this->app;
        /**
         * @var ResponseInterface $response
         */
        $response = $app[ResponseFactoryInterface::class]->createResponse($code);
        if ($code >= 400) {
            $path = $app('templates_package', 'ui_http_kernel') . '::';
            if ($exception && config('debug')) {
                $view = View::make($path . "exception", ['error' => $exception]);
            } else {
                $view = View::make($path . "error", ['response' => $response]);
            }
            $response->getBody()->write($view->__toString());
        }

        return $response;

    }


}