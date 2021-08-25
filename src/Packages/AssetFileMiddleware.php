<?php

namespace Dissonance\Packages;

use Dissonance\Mimetypes\MimeTypesMini;

use Psr\Http\Message\{ResponseFactoryInterface, ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};
use function _DS\app;


class AssetFileMiddleware implements MiddlewareInterface
{

    /**
     * @var string
     */
    protected $path;
    /**
     * @var AssetsRepositoryInterface
     */
    protected $resources;
    /**
     * @var ResponseFactoryInterface
     */
    protected $response_factory;

    /**
     * AssetFileRequestMiddleware constructor.
     * @param string $path Базовая директория для перехвата запросов
     * @param AssetsRepositoryInterface $resources Репозиторий Файлов пакетов
     * @param ResponseFactoryInterface $factory Фабрика ответа
     */
    public function __construct(string $path, AssetsRepositoryInterface $resources, ResponseFactoryInterface $factory)
    {
        $this->path = $path;
        $this->resources = $resources;
        $this->response_factory = $factory;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        $pattern = '~^' . preg_quote(trim($this->path, '/'), '~') . '/(.[^/]+)(.+)~i';

        $assets_repository = $this->resources;
        if (preg_match($pattern, ltrim($request->getRequestTarget(), '/'), $match)) {

            /**
             * @var  \Dissonance\Http\PsrHttpFactory|ResponseFactoryInterface $response_factory
             */
            $response_factory = $this->response_factory;
            try {
                $file = $assets_repository->getAssetFileStream($match[1], $match[2]);
                /**
                 * @var MimeTypesMini $mime_types
                 */
                $mime_types = new MimeTypesMini();

                return $response_factory->createResponse(200)
                    ->withBody($file)
                    ->withHeader('content-type', $mime_types->getMimeType($match[2]))
                    ->withHeader('content-length', $file->getSize());

            } catch (\Throwable $e) {
                app()->set('destroy_response', true);
                return $response_factory->createResponse(404);
            }
        }

        return $handler->handle($request);
    }

}