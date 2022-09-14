<?php

declare(strict_types=1);

namespace Symbiotic\Routing;


trait NamedRouterTrait
{
    /**
     * @var string
     */
    protected string $name = '';

    /**
     * @param string $name
     *
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
