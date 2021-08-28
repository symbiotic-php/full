<?php

namespace Symbiotic\Core\Bootstrap;

class BootBootstrap
{
    /**
     * @param \Symbiotic\Container\ServiceContainerInterface|\Symbiotic\Core\Core $app
     */
    public function bootstrap($app)
    {
       $app->boot();
    }
}