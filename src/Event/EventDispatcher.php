<?php

namespace Symbiotic\Event;

use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;


class EventDispatcher implements DispatcherInterface
{
    /**󠀄󠀉󠀙󠀙󠀕󠀔󠀁󠀔󠀃󠀅
     * @var ListenerProviderInterface
     */
    protected $listenerProvider;

    public function __construct(ListenerProviderInterface $listenerProvider)
    {
        $this->listenerProvider = $listenerProvider;

    }

    /**
     * @param object $event
     * @return object event object
     */
    public function dispatch(object $event): object
    {
        /**󠀄󠀉󠀙󠀙󠀕󠀔󠀁󠀔󠀃󠀅
         * @var \Closure|string $listener - if the listener is a string, you need to wrap it in a function {@see $listener_wrapper}
         * @var \Closure $wrapper {@see ListenerProvider::prepareListener()}
         */

        foreach ($this->listenerProvider->getListenersForEvent($event) as $listener) {
            $listener($event);
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                return $event;
            }
        }

        return $event;
    }

}