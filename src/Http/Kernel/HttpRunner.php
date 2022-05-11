<?php

namespace Symbiotic\Http\Kernel;

use Psr\Http\Message\{ResponseFactoryInterface, ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;
use Symbiotic\Core\{CoreInterface, HttpKernelInterface, Runner};
use Symbiotic\Http\{PsrHttpFactory, ResponseSender, UriHelper};
use Symbiotic\Http\Middleware\{MiddlewareCallback, MiddlewaresHandler, RequestPrefixMiddleware};


class HttpRunner extends Runner
{

    protected string $public_path = '';

    public function isHandle(): bool
    {
        return $this->core['env'] === 'web';
    }

    public function run(): bool
    {

        /**
         * @var CoreInterface $core
         */
        $core = $this->core;
        $symbiosis = \_S\config('symbiosis', true);
        try {
            $request_interface = ServerRequestInterface::class;
            // Занимает половину времени , видимо из-за инклюда файлов
            $request = $core[PsrHttpFactory::class]->createServerRequestFromGlobals();
            $core->instance($request_interface, $request, 'request');
            $core->alias($request_interface, \get_class($request));

            // удаляем путь до скрипта
            $base_uri = $this->prepareBaseUrl($request);
            $core['base_uri'] = $base_uri;
            $core['public_path'] = $this->public_path;
            $core['original_request'] = $request;
            $request = $request->withUri((new UriHelper())->deletePrefix($base_uri, $request->getUri()));

            /**
             * Через событие вы можете добавить посредников до загрузки Http ядра и всех провайдеров
             * удобно когда надо быстро ответить, рекомендуется использовать в крайней необходимости
             */
            $handler = \_S\event(new PreloadKernelHandler($this->getHttpKernel()));
            // ставим в начало проверку префикса
            $handler->prepend(new RequestPrefixMiddleware(
                $core[ResponseFactoryInterface::class],
                $core('config::uri_prefix', null)
            ));
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
            $response = $handler->handle($request);

            // Определяем нужно ли отдавать ответ
            if (!$core('destroy_response', false) || !$symbiosis) {
                $this->sendResponse($response);
                // при режиме симбиоза не даем другим скриптам продолжить работу, т.к. отдали наш ответ
                return true;
            } else {
                // Todo: Открываем проксирование буфера через нас
            }

        } catch (\Throwable $e) {
            // при режиме симбиоза не отдаем ответ с ошибкой, запишем выше в лог
            if (!$symbiosis) {
                $this->sendResponse($core[HttpKernelInterface::class]->response(500, $e));
                return true;
            } else {
                // TODO:log
            }
        }
        return false;
    }

    protected function prepareBaseUrl(ServerRequestInterface $request): string
    {
        $server = $request->getServerParams();
        $baseUrl = '/';
        $app = $this->core;
        if (PHP_SAPI !== 'cli') {
            foreach (['PHP_SELF', 'SCRIPT_NAME', 'ORIG_SCRIPT_NAME'] as $v) {
                $value = $server[$v];

                if (!empty($value) && basename($value) == basename($server['SCRIPT_FILENAME'])) {
                    //  $this->file = basename($value);
                    $this->public_path = str_replace($value, '', $server['SCRIPT_FILENAME']);
                    $request_uri = $request->getUri()->getPath();
                    $value = '/' . ltrim($value, '/');
                    if ($request_uri === preg_replace('~^' . preg_quote($value, '~') . '~i', '', $request_uri)) {

                        if (is_null($app('mod_rewrite'))) {
                            $app['mod_rewrite'] = true;
                        }
                        $value = dirname($value);
                    }
                    $baseUrl = $value;
                    break;
                }
            }
        }

        return rtrim($baseUrl, '/\\');
    }

    protected function getHttpKernel(): HttpKernelInterface
    {
        return $this->core->make(HttpKernelInterface::class);
    }

    public function sendResponse(ResponseInterface $response)
    {
        $sender = new ResponseSender($response);
        $sender->render();
        if (\function_exists('fastcgi_finish_request')) {
            \register_shutdown_function(function () {
                \fastcgi_finish_request();
            });

        } elseif (!\in_array(\PHP_SAPI, ['cli', 'phpdbg'], true)) {
            static::closeOutputBuffers(0, true);
        }
    }

    /**
     * Laravel close buffers
     * @param int $targetLevel
     * @param bool $flush
     */
    public static function closeOutputBuffers(int $targetLevel, bool $flush): void
    {
        $status = \ob_get_status(true);
        $level = \count($status);
        $flags = \PHP_OUTPUT_HANDLER_REMOVABLE | ($flush ? \PHP_OUTPUT_HANDLER_FLUSHABLE : \PHP_OUTPUT_HANDLER_CLEANABLE);

        while ($level-- > $targetLevel && ($s = $status[$level]) && (!isset($s['del']) ? !isset($s['flags']) || ($s['flags'] & $flags) === $flags : $s['del'])) {
            if ($flush) {
                ob_end_flush();
            } else {
                ob_end_clean();
            }
        }
    }

    protected function preparePublicPath()
    {

    }

}