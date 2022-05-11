<?php

namespace Symbiotic\Packages;


class ResourceException extends \RuntimeException implements ResourceExceptionInterface
{
    protected string $path = '';

    public function __construct($message = "", string $path = '', $code = 1374, \Throwable $previous = null)
    {
        $this->path = $path;
        parent::__construct($message, $code, $previous);
    }

    public function getPath(): string
    {
        return $this->path;
    }
}