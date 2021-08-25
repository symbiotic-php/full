<?php

namespace Dissonance\Routing;


/**
 * Interface NamedRouterInterface
 * @package Dissonance\Routing
 */
interface NamedRouterInterface
{
    public function setName(string $name);

    public function getName() : string;
}
