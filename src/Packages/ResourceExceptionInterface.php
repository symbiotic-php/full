<?php

namespace Symbiotic\Packages;


interface ResourceExceptionInterface extends \Throwable
{
    public function getPath(): string;
}


