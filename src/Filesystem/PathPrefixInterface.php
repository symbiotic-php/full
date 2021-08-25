<?php

namespace Dissonance\Filesystem;


interface PathPrefixInterface
{
    public function setPathPrefix($path);

    public function getPathPrefix();

    public function applyPathPrefix($path);

    public function removePathPrefix($path);
}
