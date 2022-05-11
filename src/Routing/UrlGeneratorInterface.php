<?php

namespace Symbiotic\Routing;

interface UrlGeneratorInterface
{
    /**
     * @param string $path
     * @return string
     */
    public function asset(string $path = '');

    /**
     * @param string $path
     * @return string
     */
    public function to(string $path = '');

    /**
     * @param $name
     * @param array $parameters
     * @param bool $absolute
     * @return string
     * @throws \Exception
     */
    public function route(string $name, array $parameters = [], bool $absolute = true);
}