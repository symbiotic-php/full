<?php

namespace Dissonance\Routing;

/**
 * Trait NamedRouterTrait
 * @package Dissonance\Routing
 *
 */
trait NamedRouterTrait
{
    protected $name = '';

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
