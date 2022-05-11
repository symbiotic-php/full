<?php

namespace Symbiotic\Http\Kernel;

use Psr\Http\Message\{ResponseFactoryInterface, ResponseInterface, ServerRequestInterface, StreamInterface};
use Psr\Http\Server\RequestHandlerInterface;
use Symbiotic\Apps\{ApplicationInterface, AppsRepositoryInterface};
use Symbiotic\Core\{CoreInterface, Support\ArrayableInterface, Support\RenderableInterface};
use Symbiotic\Http\ResponseMutable;
use Symbiotic\Routing\RouteInterface;


class RouteHandler implements RequestHandlerInterface
{
    /**
     * @var CoreInterface
     */
    protected CoreInterface $core;

    /**
     * @var RouteInterface
     */
    protected RouteInterface $route;

    public function __construct(CoreInterface $app, RouteInterface $route)
    {
        $this->core = $app;
        $this->route = $route;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {

        $app = $this->core;


        /**
         * @var RouteInterface $route
         * @var CoreInterface|ApplicationInterface $container
         * @var AppsRepositoryInterface|null $apps
         * @var callable|string|null $handler
         */
        $route = $app[RouteInterface::class] = $this->route;
        $app->alias(RouteInterface::class, 'route');
        $apps = $app[AppsRepositoryInterface::class];
        $action = $route->getAction();

        $container = (isset($action['app']) && ($apps instanceof AppsRepositoryInterface)) ? $apps->getBootedApp($action['app']) : $this->core;

        $handler = $route->getHandler();
        if (!is_string($handler) && !is_callable($handler)) {
            throw new \Exception('Incorrect route handler for route ' . $route->getPath() . '!');
        }
        // Раздаем запрос
        $request_interface = ServerRequestInterface::class;
        $app->instance($request_interface, $request, 'request');
        $app->alias($request_interface, \get_class($request));

        // Ставим мутабельный объект ответа для контроллеров и экшенов
        $response = new ResponseMutable($app[ResponseFactoryInterface::class]->createResponse());
        $app->instance(ResponseInterface::class, $response, 'response');

        return $this->prepareResponse($container->call($handler, $route->getParams()), $response);

    }

    protected function prepareResponse($data, ResponseMutable $response): ResponseInterface
    {
        if ($data instanceof ResponseInterface) {
            return $data;
        } elseif ($data instanceof StreamInterface) {
            return $response->withBody($data)->getRealInstance();
        }

        if (is_array($data) || $data instanceof \Traversable || $data instanceof ArrayableInterface || $data instanceof \JsonSerializable) {
            $response->withHeader('content-type', 'application/json');
            $data = \_S\collect($data)->__toString();
        } elseif ($data instanceof RenderableInterface
            || (is_object($data) && \method_exists($data, '__toString')) /*$data instanceof \Stringable*/) {
            $data = $data->__toString();
        }
        // пишем строкой, если вам необходимо отдать большой объем контента, то сразу отдавайте Response из обработчика
        $response->getBody()->write((string)$data);
        return $response->getRealInstance();
    }

}