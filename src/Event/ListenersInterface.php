<?php

namespace Symbiotic\Event;

use \Psr\EventDispatcher\ListenerProviderInterface;

interface ListenersInterface extends ListenerProviderInterface
{
    /**
     * @param string $event the class name or an arbitrary event name
     * @param \Closure|string $handler function or class name of the handler
     * The event handler class must implement the handle method  (...$params) or __invoke(...$params)
     * @return void
     */
    public function add(string $event, string|\Closure $handler): void;
}
