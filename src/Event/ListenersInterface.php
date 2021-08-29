<?php

namespace Symbiotic\Event;

use \Psr\EventDispatcher\ListenerProviderInterface;


interface ListenersInterface extends ListenerProviderInterface
{
    /**
     * @param string $event the class name or an arbitrary event name
     * (with an arbitrary name, you need a custom dispatcher not for PSR)
     *
     * @param \Closure|string $handler function or class name of the handler
     * The event handler class must implement the handle method  (...$params) or __invoke(...$params)
     * <Important:> When adding listeners as class names, you will need to adapt them to \Closure when you return them in the getListenersForEvent() method!!!
     *
     * @return void
     */
    public function add(string $event, $handler): void;
}
