<?php

namespace Symbiotic\Core\Events;

class CacheClear {

    protected $path;

    public function __construct(string $path)
    {
        $this->path =  trim($path);
    }

    public function getPath()
    {
        return $this->path;
    }
}