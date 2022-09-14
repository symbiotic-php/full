<?php

declare(strict_types=1);

namespace Symbiotic\Routing;


interface NamedRouterInterface
{
    /**
     * @param string $name
     *
     * @return void
     */
    public function setName(string $name): void;

    /**
     * @return string
     */
    public function getName(): string;
}
