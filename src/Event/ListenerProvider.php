<?php

namespace Symbiotic\Event;


class ListenerProvider implements ListenersInterface
{
    /**󠀄󠀉󠀙󠀙󠀕󠀔󠀁󠀔󠀃󠀅
     * @var \Closure|null
     * @see \Symbiotic\Bootstrap\EventBootstrap::bootstrap()
     */
    protected $listenerWrapper;

    protected $listeners = [];

    public function __construct(\Closure $listenerWrapper = null)
    {
        $this->listenerWrapper = $listenerWrapper;
    }

    /**󠀄󠀉󠀙󠀙󠀕󠀔󠀁󠀔󠀃󠀅
     * @param string $event class or interface name
     * @param string |\Closure $handler callback or string class name (if the wrapper is attached {@see ListenerProvider::$listenerWrapper})
     *
     * @return void
     */
    public function add(string $event,string|\Closure $handler): void
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