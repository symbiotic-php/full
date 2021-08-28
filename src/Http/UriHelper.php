<?php

namespace Symbiotic\Http;

use Psr\Http\Message\UriInterface;

class UriHelper
{
    public function deletePrefix(string $prefix, UriInterface $uri):UriInterface
    {
        $prefix = $this->normalizePrefix($prefix);
        if(!empty($prefix)) {
            $path = $uri->getPath();
            $path = preg_replace('~^'.preg_quote($prefix,'~').'~','', $path);
            $uri = $uri->withPath($path);
        }

        return $uri;

    }

    public  function normalizePrefix(string $prefix) : string
    {
        $prefix = trim($prefix, ' \\/');

        return $prefix == '' ? '' : '/'.$prefix;
    }
}