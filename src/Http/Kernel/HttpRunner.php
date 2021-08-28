<?php

namespace Dissonance\Http\Kernel;

use Dissonance\Core\{CoreInterface, HttpKernelInterface, Runner};
use Dissonance\Http\ {ResponseSender, PsrHttpFactory, UriHelper};
use Psr\Http\Message\ {ResponseFactoryInterface, ServerRequestInterface, ResponseInterface};
use Dissonance\Http\Middleware\ {MiddlewareCallback, MiddlewaresHandler, RequestPrefixMiddleware};
use Psr\Http\Server\RequestHandlerInterface;
use Dissonance\Packages\AssetFileMiddleware;


class HttpRunner extends Runner
{

    public function isHandle(): bool
    {
        return $this->app['env'] === 'web';
    }

    public function run(): void
    {
        /**
         * @var CoreInterface $app
         */
        $app = $this->app;
        $symbiosis = \_DS\config('symbiosis',false);
        try {
            $request_interface = ServerRequestInterface::class;
            $request = $app[PsrHttpFactory::class]->createServerRequestFromGlobals();
            $app->instance($request_interface, $request, 'request');
            $app->alias($request_interface, get_class($request));

            $base_uri = $this->prepareBaseUrl($request);
            $app['base_uri'] = $base_uri;
            $app['original_request'] = $request;
            $request = $request->withUri((new UriHelper())->deletePrefix($base_uri, $request->getUri()));
            /**
             * Через событие вы можете добавить посредников до загрузки Http ядра и всех провайдеров
             * удобно когда надо быстро ответить, рекомендуется использовать в крайней необходимости
             */
            $handler = $app['events']->dispatch(
                new PreloadKernelHandler($app->make(HttpKernelInterface::class))
            );
            $handler->prepend(new RequestPrefixMiddleware($app('config::uri_prefix', null)));
            $handler->append(
                new MiddlewareCallback(function (ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
                    if ($handler instanceof MiddlewaresHandler) {
                        $real = $handler->getRealHandler();
                        if ($real instanceof HttpKernelInterface) {
                            $real->bootstrap();
                        }
                    }
                    return $handler->handle($request);
                })
            );
            $response = $handler->handle($request);

            // Определяем нужно ли отдавать ответ
            if (!$app('destroy_response', false) || !$symbiosis) {
                $this->sendResponse($response);
                // при режиме симбиоза не даем другим скриптам продолжить работу, т.к. отдали наш ответ
                if ($symbiosis) {
                    exit;// завершаем работу
                }
            } else {
                // Открываем проксирование буфера через нас
            }

        } catch (\Throwable $e) {
            // при режиме симбиоза не отдаем ответ с ошибкой, запишем выше в лог
            if (!$symbiosis) {
                $this->sendResponse($app[HttpKernelInterface::class]->response(500, $e));
            } else {
                // TODO:log
            }
        }
    }


    /**
     * Laravel close buffers
     * @param int $targetLevel
     * @param bool $flush
     */
    public static function closeOutputBuffers(int $targetLevel, bool $flush): void
    {
        $status = ob_get_status(true);
        $level = \count($status);
        $flags = \PHP_OUTPUT_HANDLER_REMOVABLE | ($flush ? \PHP_OUTPUT_HANDLER_FLUSHABLE : \PHP_OUTPUT_HANDLER_CLEANABLE);

        while ($level-- > $targetLevel &&  ($s = $status[$level])  && (!isset($s['del']) ? !isset($s['flags']) || ($s['flags'] & $flags) === $flags : $s['del'])) {
            if ($flush) {
                ob_end_flush();
            } else {
                ob_end_clean();
            }
        }
    }

    protected function prepareBaseUrl(ServerRequestInterface $request): string
    {
        $server = $request->getServerParams();
        $baseUrl = '/';
        if (PHP_SAPI !== 'cli') {
            foreach (['PHP_SELF', 'SCRIPT_NAME', 'ORIG_SCRIPT_NAME'] as $v) {
                $value = $server[$v];

                if (!empty($value) && basename($value) == basename($server['SCRIPT_FILENAME'])) {
                    $this->file = basename($value);
                    $request_uri = $request->getUri()->getPath();
                    $value = '/' . ltrim($value, '/');
                    if ($request_uri === preg_replace('~^' . preg_quote($value, '~') . '~i', '', $request_uri)) {
                        $app = $this->app;
                        if (is_null($app('mod_rewrite'))) {
                            $this->app['mod_rewrite'] = true;
                        }
                        $value = dirname($value);
                    }
                    $baseUrl = $value;
                    break;
                }
            }
        }

        return rtrim($baseUrl, '/' . \DIRECTORY_SEPARATOR);
    }


    public function sendResponse(ResponseInterface $response)
    {
        $sender = new ResponseSender($response);
        $sender->render();
        if (\function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } elseif (!\in_array(\PHP_SAPI, ['cli', 'phpdbg'], true)) {
            static::closeOutputBuffers(0, true);
        }
    }

}