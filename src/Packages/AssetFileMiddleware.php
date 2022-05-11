<?php

namespace Symbiotic\Packages;

use Psr\Http\Message\{ResponseFactoryInterface, ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};
use Symbiotic\Mimetypes\MimeTypesMini;
use function _S\core;


class AssetFileMiddleware implements MiddlewareInterface
{

    /**
     * @var string
     */
    protected string $path;

    /**
     * @var AssetsRepositoryInterface
     */
    protected AssetsRepositoryInterface $resources;

    /**
     * @var ResponseFactoryInterface
     */
    protected ResponseFactoryInterface $response_factory;

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
        if (preg_match($pattern, ltrim($request->getUri()->getPath(), '/'), $match)) {

            /**
             * @var  \Symbiotic\Http\PsrHttpFactory|ResponseFactoryInterface $response_factory
             */
            $response_factory = $this->response_factory;
            try {
                $file = $assets_repository->getAssetFileStream($match[1], $match[2]);

                $mime_types = new MimeTypesMini();
                $mime = $mime_types->getMimeType($match[2]);
                if (!$mime) {
                    $mime = 'text/plain';
                }
                return $response_factory->createResponse(200)
                    ->withBody($file)
                    ->withHeader('content-type', $mime)
                    ->withHeader('Cache-Control', 'max-age=86400')
                    ->withHeader('content-length', $file->getSize());

            } catch (\Throwable $e) {
                core()->set('destroy_response', true);
                return $response_factory->createResponse(404);
            }
        }

        return $handler->handle($request);
    }

}