<?php

namespace Symbiotic\Routing;


/**
 * Interface NamedRouterInterface
 * @package Symbiotic\Routing
 */
interface NamedRouterInterface
{
    public function setName(string $name);

    public function getName() : string;
}
