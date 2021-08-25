<?php

namespace Dissonance\Core\Bootstrap;

class BootBootstrap
{
    /**
     * @param \Dissonance\Container\ServiceContainerInterface|\Dissonance\Core\Core $app
     */
    public function bootstrap($app)
    {
       $app->boot();
    }
}