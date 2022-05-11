<?php

namespace Symbiotic\Packages;


use Symbiotic\Core\{AbstractBootstrap, CoreInterface};
use Symbiotic\Event\ListenerProvider;


class PackagesBootstrap extends AbstractBootstrap
{

    public function bootstrap(CoreInterface $core): void
    {
        $packages_class = PackagesRepositoryInterface::class;
        $core->singleton($packages_class, function () {
            return new PackagesRepository;
        });// todo yfghhzve. cjplfnm
        //$this->cached($app, $packages_class, PackagesRepository::class);
        /**
         * @var PackagesRepositoryInterface $p
         */
        $p = $core[$packages_class];
        $p->load();
        foreach ($p->getBootstraps() as $v) {
            if ($v === get_class($this)) {
                continue;
            }
            $core->runBootstrap($v);
        }
        /**
         * add Events handlers
         */
        /*$listeners = new ListenerProvider(EventBootstrap::getListenerWrapper($app), $p->getEventsHandlers());
        $dispatcher = new CompositeEventDispatcher();
        $dispatcher->attach($app[EventDispatcherInterface::class]);
        $dispatcher->attach(new EventDispatcher($listeners));

        $app[EventDispatcherInterface::class] = $dispatcher;*/
        /**
         * @var ListenerProvider $listener
         */
        $listener = $core['listeners'];
        foreach ($p->getEventsHandlers() as $event => $handlers) {
            foreach ($handlers as $v) {
                $listener->add($event, $v);
            }
        }
        /**
         * Для расширений ядра
         */
        /* $resolvers = $p->getResolvers();
         foreach ($p->all() as $package_config) {
             foreach ($resolvers as $callback) {
                 $callback($package_config);
             }
         }*/

    }
}
