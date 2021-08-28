<?php

namespace Symbiotic\Http\Middleware;

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
    protected ?string $uri_prefix;

    /**
     * RequestPrefixMiddleware constructor.
     * @param $uri_prefix - set in the Core container constructor config $app['config::uri_prefix'] {@see /config.php}
     */
    public function __construct(string $uri_prefix = null)
    {
        $this->uri_prefix = $uri_prefix;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @todo: UriHelper delete?
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        return $handler->handle(
            empty($this->uri_prefix)
                        ? $request
                        : $request->withUri((new UriHelper())->deletePrefix($this->uri_prefix, $request->getUri()))
        );


    }
}