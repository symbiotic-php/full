<?php

namespace Symbiotic\Container;


class SubContainer implements DIContainerInterface, ContextualBindingsInterface
{
    use SubContainerTrait;

    public function __construct(DIContainerInterface $container)
    {
        $this->app = $container;
    }
}
