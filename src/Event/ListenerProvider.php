<?php

namespace Symbiotic\Event;


class ListenerProvider implements ListenersInterface
{
    /**󠀄󠀉󠀙󠀙󠀕󠀔󠀁󠀔󠀃󠀅
     * @var \Closure|null
     * @see \Symbiotic\Core\Bootstrap\EventBootstrap::bootstrap()
     */
    protected $listenerWrapper;

    protected $listeners = [];

    public function __construct(\Closure $listenerWrapper = null)
    {
        $this->listenerWrapper = $listenerWrapper;
    }

    /**󠀄󠀉󠀙󠀙󠀕󠀔󠀁󠀔󠀃󠀅
     * @param string $event the class name or an arbitrary event name
     * (with an arbitrary name, you need a custom dispatcher not for PSR)
     *
     * @param \Closure|string $handler function or class name of the handler
     * The event handler class must implement the handle method  (...$params) or __invoke(...$params)
     * <Important:> When adding listeners as class names, you will need to adapt them to \Closure when you return them in the getListenersForEvent() method!!!
     *
     * @return void
     */
    public function add(string $event, $handler): void
    {
        $this->listeners[$event][] = $handler;
    }

    /**󠀄󠀉󠀙󠀙󠀕󠀔󠀁󠀔󠀃󠀅
     * @param object $event
     *
     * @return iterable|\Closure[]
     */
    public function getListenersForEvent(object $event): iterable
    {
        $parents = \class_parents($event);
        $implements = \class_implements($event);
        $classes = array_merge([\get_class($event)], $parents ?: [], $implements ?: []);
        $listeners = [];
        foreach ($classes as $v) {
            $listeners = array_merge($listeners, isset($this->listeners[$v]) ? $this->listeners[$v] : []);
        }
        $wrapper = $this->listenerWrapper;

        return $wrapper ? array_map(function ($v) use ($wrapper) {
            return $wrapper($v);
        }, $listeners) : $listeners;

    }
}