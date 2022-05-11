<?php
/*
namespace Symbiotic\Http\Kernel;

use Psr\Http\Message\{ResponseFactoryInterface, ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;
use Symbiotic\Core\{CoreInterface, HttpKernelInterface};
use Symbiotic\Http\{UriHelper};
use Symbiotic\Http\Middleware\{MiddlewareCallback, MiddlewaresHandler, RequestPrefixMiddleware};*/

/**
 * Класс на будущее, будет обрабатывать запросы в виде процесса
 * Class HttpHandler
 * @package Symbiotic\Http\Kernel
 */
/*class HttpHandler implements RequestHandlerInterface
{
    /!**
     * @var CoreInterface
     *!/
    protected CoreInterface $core;

    public function __construct(CoreInterface $core)
    {
        $this->core = $core;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {

        $core = $this->core;

        // удаляем путь до скрипта
        $base_uri = $this->prepareBaseUrl($request);
        $core['base_uri'] = $base_uri;
        $core['original_request'] = $request;
        $request = $request->withUri((new UriHelper())->deletePrefix($base_uri, $request->getUri()));

        /!!**
         * Через событие вы можете добавить посредников до загрузки Http ядра и всех провайдеров
         * удобно когда надо быстро ответить, рекомендуется использовать в крайней необходимости
         *!!/
        $handler = $core['events']->dispatch(
            new PreloadKernelHandler($this->getHttpKernel())
        );
        // ставим в начало проверку префикса
        $handler->prepend(new RequestPrefixMiddleware($core('config::uri_prefix', null), $core[ResponseFactoryInterface::class]));
        // ставим в конец загрузку провайдеров Http Ядра.
        $handler->append(
            new MiddlewareCallback(function (ServerRequestInterface $request, RequestHandlerInterface $handler) use ($core): ResponseInterface {
                $core->runBefore();
                if ($handler instanceof MiddlewaresHandler) {
                    $real = $handler->getRealHandler();
                    if ($real instanceof HttpKernelInterface) {
                        $real->bootstrap();
                    }
                }
                return $handler->handle($request);
            })
        );

        return $handler->handle($request);


    }

    protected function prepareBaseUrl(ServerRequestInterface $request): string
    {
        $server = $request->getServerParams();
        $baseUrl = '/';
        $core = $this->core;
        foreach (['PHP_SELF', 'SCRIPT_NAME', 'ORIG_SCRIPT_NAME'] as $v) {
            $value = $server[$v];

            if (!empty($value) && basename($value) === basename($server['SCRIPT_FILENAME'])) {
                // $this->file = basename($value);
                $request_uri = $request->getUri()->getPath();

                $normalized = \str_replace('\\', '/', [$core('base_path', ''), $value]);
                // В режиме cli полный путь от корня, вырезаем
                $value = \str_replace($normalized[0], '', $normalized[1]);
                $value = '/' . ltrim($value, '/');
                if ($request_uri === preg_replace('~^' . preg_quote($value, '~') . '~i', '', $request_uri)) {

                    if (is_null($core('mod_rewrite'))) {
                        $core['mod_rewrite'] = true;
                    }
                    $value = dirname($value);
                }
                $baseUrl = $value;
                break;
            }
        }


        return rtrim($baseUrl, '/' . \DIRECTORY_SEPARATOR);
    }

    protected function getHttpKernel(): HttpKernelInterface
    {
        return $this->core->make(HttpKernelInterface::class);
    }

}*/