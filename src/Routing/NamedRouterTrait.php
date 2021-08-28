<?php

namespace Symbiotic\Routing;

/**
 * Trait NamedRouterTrait
 * @package Symbiotic\Routing
 *
 */
trait NamedRouterTrait
{
    protected string $name = '';

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
