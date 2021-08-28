<?php

namespace Symbiotic\Container;


use Psr\Container\ContainerInterface;

class SubContainer  implements DIContainerInterface, ContextualBindingsInterface
{
    use SubContainerTrait;

    public function __construct(ContainerInterface $container)
    {
        $this->app  = $container;
    }
}
