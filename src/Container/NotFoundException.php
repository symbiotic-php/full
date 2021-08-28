<?php

namespace Symbiotic\Container;

use Psr\Container\NotFoundExceptionInterface;
use Throwable;

class NotFoundException extends ContainerException implements NotFoundExceptionInterface
{
    public function __construct($key, $container, $code = 1384, Throwable $previous = null)
    {
        $message = 'Not found key [' . $key . '] in (' . (is_object($container) ? get_class($container) : gettype($container)) . ')!';
        parent::__construct($message, $code, $previous);
    }
}
