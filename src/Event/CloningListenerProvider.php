<?php

namespace Symbiotic\Event;

use Psr\Container\ContainerInterface;
use Symbiotic\Container\CloningContainer;
use Symbiotic\Core\Bootstrap\EventBootstrap;


class CloningListenerProvider implements ListenersInterface, CloningContainer
{
    /**
     * @param ListenersInterface $listenerProvider
     */
    public function __construct(protected ListenersInterface $listenerProvider)
    {
    }

    /**
     * @inheritDoc
     *
     * @param object $event
     *
     * @return iterable
     */
    public function getListenersForEvent(object $event): iterable
    {
        return $this->listenerProvider->getListenersForEvent($event);
    }

    /**
     * @inheritDoc
     *
     * @param string          $event
     * @param \Closure|string $handler
     *
     * @return void
     */
    public function add(string $event, \Closure|string $handler): void
    {
        $this->listenerProvider->add($event, $handler);
    }

    /**
     * @inheritDoc
     *
     * @param ContainerInterface|null $container
     *
     * @return $this|null
     */
    public function cloneInstance(?ContainerInterface $container): ?static
    {
        $new = clone $this;

        $listenerProvider = clone $this->listenerProvider;
        if ($listenerProvider instanceof ListenerProvider) {
            $listenerProvider->setWrapper(EventBootstrap::getListenerWrapper($container));
        }
        $new->listenerProvider = $listenerProvider;

        return $new;
    }
}