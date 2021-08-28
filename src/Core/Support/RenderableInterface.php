<?php

namespace Symbiotic\Core\Support;

interface RenderableInterface /*extends \Stringable we support 7.x versions */
{
    public function render();

    public function __toString();
}