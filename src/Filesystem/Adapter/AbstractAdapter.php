<?php

namespace Dissonance\Filesystem\Adapter;

use Dissonance\Filesystem\AdapterInterface;
use Dissonance\Filesystem\PathPrefixInterface;

abstract class AbstractAdapter implements AdapterInterface,PathPrefixInterface
{

    protected $path_prefix = '/';


    public function setPathPrefix($path)
    {
        if(!empty($path)) {
            $this->path_prefix = rtrim($path,'\\/').'/';
        }
        return $this;
    }

    public function getPathPrefix()
    {
        return  $this->path_prefix;
    }

    public function applyPathPrefix($path)
    {
        return $this->getPathPrefix().ltrim($path,'\\/');
    }

    public function removePathPrefix($path)
    {
        return str_replace($this->getPathPrefix(),'', $path);
    }

    public function normalizePath($path)
    {
        $path = rtrim(str_replace("\\", "/", trim($path)), '/');
        $unx = (strlen($path) > 0 && $path[0] == '/');
        $parts = array_filter(explode('/', $path), 'strlen');
        $absolutes = [];
        foreach ($parts as $part) {
            if ('.' == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        $path = implode('/', $absolutes);
        $path = $unx ? '/' . $path : $path;

        return $path;
    }
}
