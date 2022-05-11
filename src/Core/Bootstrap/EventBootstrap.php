<?php

namespace Symbiotic\Core\Bootstrap;


use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Symbiotic\Container\DIContainerInterface;
use Symbiotic\Core\BootstrapInterface;
use Symbiotic\Core\CoreInterface;
use Symbiotic\Event\EventDispatcher;
use Symbiotic\Event\ListenerProvider;
use Symbiotic\Event\ListenersInterface;


class EventBootstrap implements BootstrapInterface
{

    public function bootstrap(CoreInterface $core): void
    {
        // Events listeners
        $listener_interface = ListenerProviderInterface::class;
        $core->singleton($listener_interface, function ($app) {
            return new ListenerProvider(static::getListenerWrapper($app));
        }, 'listeners')
            ->alias($listener_interface, ListenersInterface::class);

        // Events dispatcher
        $core->singleton(EventDispatcherInterface::class, EventDispatcher::class, 'events');
    }

    public static function getListenerWrapper(DIContainerInterface $app)
    {
        return function ($listener) use ($app) {

            return function (object $event) use ($listener, $app) {
                if (is_string($listener) && class_exists($listener)) {
                    $handler = $app->make($listener);
                    if (method_exists($handler, 'handle') || is_callable($handler)) {
                        return $app->call([$handler, method_exists($handler, 'handle') ? 'handle' : '__invoke'], ['event' => $event]);
                    }
                    return null;
                } elseif ($listener instanceof \Closure) {
                    return $app->call($listener, ['event' => $event]);
                }
            };
        };
    }

}
