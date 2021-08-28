<?php

namespace Symbiotic\Core;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface HttpKernelInterface extends RequestHandlerInterface
{
    public function bootstrap() : void;

    /**
     * @param int $code
     * @param  \Throwable |null $exception
     * @return ResponseInterface
     */
    public function response(int $code = 200, \Throwable $exception = null): ResponseInterface;
}
