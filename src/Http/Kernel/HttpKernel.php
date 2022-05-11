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
use function _S\config;


class HttpKernel implements HttpKernelInterface
{
    /**
     * @var CoreInterface
     */
    protected CoreInterface $core;
    /**
     * @var string[]  Names of classes implements from {@uses \Symbiotic\Core\BootstrapInterface}
     */
    protected array $bootstrappers = [];

    protected ?bool $mod_rewrite = null;


    public function __construct(CoreInterface $container)
    {
        $this->core = $container;
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
        if (!$this->core->isBooted()) {
            $this->core->addBootstraps($this->bootstrappers);
            $this->core->bootstrap();
        }
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {


        $core = $this->core;


        $dispatcher = $core->instance(MiddlewaresDispatcher::class, new MiddlewaresDispatcher());
        $dispatcher->setDefaultCallback(function ($class) use ($core) {
            /**
             * @var ContainerInterface|FactoryInterface $core
             */
            return $core->make($class);
        });

        return (new MiddlewaresCollection(
                \_S\event($dispatcher)
                    ->factoryGroup(MiddlewaresDispatcher::GROUP_GLOBAL)

                ))->process($request, $core->make(RoutingHandler::class));
    }


    /**
     * @param int $code
     * @param \Throwable |null $exception
     * @return ResponseInterface
     */
    public function response(int $code = 200, \Throwable $exception = null): ResponseInterface
    {
        $app = $this->core;
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