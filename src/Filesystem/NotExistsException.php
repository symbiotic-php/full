<?php

namespace Symbiotic\Filesystem;


use Throwable;

class NotExistsException extends \Exception
{

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = 'File not exists['.$message.']!';
        parent::__construct($message, $code, $previous);
    }
}
