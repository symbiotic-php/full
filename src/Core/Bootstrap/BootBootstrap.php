<?php

namespace Symbiotic\Core\Bootstrap;

use Symbiotic\Core\BootstrapInterface;
use Symbiotic\Core\CoreInterface;

class BootBootstrap implements BootstrapInterface
{
    /**
     * @param \Symbiotic\Container\ServiceContainerInterface|\Symbiotic\Core\Core $app
     */
    public function bootstrap(CoreInterface $core): void
    {
        $core->boot();
    }
}