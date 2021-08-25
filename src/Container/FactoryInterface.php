<?php

namespace Dissonance\Container;

/**
 * Describes the basic interface of a factory.
 *
 * @package Dissonance\Container
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 * @refactor Sergey Surkov <dissonancephp@gmail.com>
 */
interface FactoryInterface
{
    /**
     * Resolves an entry by its name. If given a class name, it will return a new instance of that class.
     *
     * @param string $name       Entry name or a class name.
     * @param array  $parameters Optional parameters to use to build the entry. Use this to force specific
     *                           parameters to specific values. Parameters not defined in this array will
     *                           be automatically resolved.
     *
     * @throws \InvalidArgumentException The name parameter must be of type string.
     * @throws BindingResolutionException       Error while resolving the entry.
     * @throws NotFoundException entry or class found for the given name.
     *
     * @return mixed
     */
    public function make(string $name, array $parameters = []);
}