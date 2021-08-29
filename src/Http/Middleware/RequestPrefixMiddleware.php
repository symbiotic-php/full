<?php

namespace Symbiotic\Http\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Symbiotic\Http\UriHelper;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestPrefixMiddleware implements MiddlewareInterface
{
    /**
     * @var null|string
     */
    protected $uri_prefix = null;

    /**
     * @var ResponseFactoryInterface|null
     */
    protected $response_factory;

    /**
     * RequestPrefixMiddleware constructor.
     * @param $uri_prefix - set in the Core container constructor config $app['config::uri_prefix'] {@see /config.php}
     */
    public function __construct(string $uri_prefix = null, ResponseFactoryInterface $factory)
    {
        $this->uri_prefix = $uri_prefix;
        $this->response_factory = $factory;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @todo: UriHelper delete?
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uriHelper = new UriHelper();
        $prefix = $this->uri_prefix;
        if(!empty($prefix)) {
            $prefix = $uriHelper->normalizePrefix($this->uri_prefix);
            if(!preg_match('/^'.preg_quote($prefix,'/').'/', $request->getUri()->getPath())) {
                if(\function_exists('_DS\app')) {
                   \_DS\app()['destroy_response'] = true;// при режиме симбиоза не отдаем ответ, дальше скрипты отработают
                }
                return $this->response_factory->createResponse(404);
            }
        }
        return $handler->handle(
            empty($prefix)
                        ? $request
                        : $request->withUri($uriHelper->deletePrefix($prefix, $request->getUri()))
        );


    }
}